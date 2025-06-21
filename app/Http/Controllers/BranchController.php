<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeMode;
use Endroid\QrCode\Label\Label;

class BranchController extends Controller
{
    public function all()
    {
        $user = Auth::user();

        // Only show businesses owned or managed by the user
        $businesses = Business::where('owner_id', $user->id)
            ->orWhereHas('admins', function ($q) use ($user) {
                $q->where('admin_id', $user->id);
            })
            ->get(['id', 'name']);

        if ($businesses->isEmpty()) {
            return Inertia::render('Branches/NoBusiness');
        }

        // Get branches for all businesses
        $branches = Branch::whereHas('business', function ($q) use ($user) {
            $q->where('owner_id', $user->id)
              ->orWhereHas('admins', function ($q2) use ($user) {
                  $q2->where('admin_id', $user->id);
              });
        })->with('business')->get();

        return Inertia::render('Branches/Index', [
            'business' => null,
            'branches' => $branches,
            'businesses' => $businesses
        ]);
    }

    public function index(Business $business)
    {
        $branches = $business->branches()->with(['business'])->get();
        
        return Inertia::render('Branches/Index', [
            'business' => $business,
            'branches' => $branches,
        ]);
    }

    public function create(Business $business)
    {
        return Inertia::render('Branches/Create', [
            'business' => $business,
        ]);
    }

    public function store(Request $request, Business $business)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
        ]);

        try {
            $branch = $business->branches()->create([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'gps_latitude' => $validated['gps_latitude'] ?? null,
                'gps_longitude' => $validated['gps_longitude'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['email'],
            ]);

            // Generate barcode for the new branch
            $branch->generateBarcode();
            $branch->refresh(); // Refresh to get the updated barcode

            return redirect()->route('businesses.branches.index', $business)
                ->with('success', 'Branch created successfully.')
                ->with('branch', $branch);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create branch: ' . $e->getMessage()]);
        }
    }

    public function show(Business $business, Branch $branch)
    {
        // Check if user has access to this business
        if (!Auth::user()->canAccessBusiness($business->id)) {
            abort(403);
        }

        return Inertia::render('Branches/Show', [
            'business' => $business,
            'branch' => $branch->load('business', 'sellers')
        ]);
    }

    public function edit(Business $business, Branch $branch)
    {
        return Inertia::render('Branches/Edit', [
            'business' => $business,
            'branch' => $branch,
            'businesses' => Business::where('owner_id', Auth::id())->get(),
            'sellers' => $branch->sellers
        ]);
    }

    public function update(Request $request, Business $business, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
        ]);

        $branch->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'gps_latitude' => $validated['gps_latitude'] ?? null,
            'gps_longitude' => $validated['gps_longitude'] ?? null,
            'phone' => $validated['phone'],
            'email' => $validated['email'],
        ]);

        return redirect()->route('businesses.branches.index', $business)
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Business $business, Branch $branch)
    {
        $branch->delete();

        return redirect()->route('branches.index', $business)
            ->with('success', 'Branch deleted successfully.');
    }

    public function generateBarcode(Business $business, Branch $branch)
    {
        $barcode = $branch->generateBarcode();

        return response()->json([
            'barcode' => $barcode,
            'message' => 'Barcode generated successfully'
        ]);
    }

    public function downloadBarcode(Business $business, Branch $branch)
    {
        if (!$branch->barcode_path) {
            return response()->json(['error' => 'No barcode found'], 404);
        }

        // v6.0.8 syntax: set all options in the constructor
        $qrCode = new EndroidQrCode(
            $branch->barcode_path,
            new Encoding('UTF-8'),
            ErrorCorrectionLevel::High,
            300, // size
            10, // margin
            RoundBlockSizeMode::Margin,
            new Color(0, 0, 0), // foreground
            new Color(255, 255, 255) // background
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return response($result->getString(), 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="barcode-' . $branch->barcode_path . '.png"');
    }

    public function printBarcode(Business $business, Branch $branch)
    {
        if (!$branch->barcode_path) {
            return response()->json(['error' => 'No barcode found'], 404);
        }

        // v6.0.8 syntax: set all options in the constructor
        $qrCode = new EndroidQrCode(
            $branch->barcode_path,
            new Encoding('UTF-8'),
            ErrorCorrectionLevel::High,
            300, // size
            10, // margin
            RoundBlockSizeMode::Margin,
            new Color(0, 0, 0), // foreground
            new Color(255, 255, 255) // background
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return response($result->getString(), 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'inline; filename="barcode-' . $branch->barcode_path . '.png"');
    }
}