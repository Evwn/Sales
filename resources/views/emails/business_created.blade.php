<h2>Business Created Successfully</h2>
<p>Your business has been created in the system. Here are the details:</p>
<ul>
    <li><strong>Business Name:</strong> {{ $business->name ?? 'Not provided' }}</li>
    <li><strong>Description:</strong> {{ $business->description ?? 'Not provided' }}</li>
    <li><strong>Contact Email:</strong> {{ $business->email ?? 'Not provided' }}</li>
    <li><strong>Phone:</strong> {{ $business->phone ?? 'Not provided' }}</li>
    <li><strong>Tax Number:</strong> {{ $business->tax_number ?? 'Not provided' }}</li>
    <li><strong>Registration Number:</strong> {{ $business->registration_number ?? 'Not provided' }}</li>
    <li><strong>Industry:</strong> {{ $business->industry ?? 'Not provided' }}</li>
    <li><strong>Address:</strong> {{ $business->address ?? 'Not provided' }}</li>
    <li><strong>City:</strong> {{ $business->city ?? 'Not provided' }}</li>
    <li><strong>State:</strong> {{ $business->state ?? 'Not provided' }}</li>
    <li><strong>Country:</strong> {{ $business->country ?? 'Not provided' }}</li>
    <li><strong>Postal Code:</strong> {{ $business->postal_code ?? 'Not provided' }}</li>
    <li><strong>Website:</strong> {{ $business->website ?? 'Not provided' }}</li>
    <li><strong>Owner Name:</strong> {{ $owner ? $owner->name : 'Not provided' }}</li>
    <li><strong>Owner Email:</strong> {{ $owner ? $owner->email : 'Not provided' }}</li>
</ul>
@if($business->logo_path || $business->tax_document_path || $business->registration_document_path || $business->terms_and_conditions)
    <h3>Documents</h3>
    <ul>
        @if($business->logo_path)
            <li><strong>Logo:</strong> <a href="{{ asset('storage/' . $business->logo_path) }}">View Logo</a></li>
        @endif
        @if($business->tax_document_path)
            <li><strong>Tax Document:</strong> <a href="{{ asset('storage/' . $business->tax_document_path) }}">View Tax Document</a></li>
        @endif
        @if($business->registration_document_path)
            <li><strong>Registration Document:</strong> <a href="{{ asset('storage/' . $business->registration_document_path) }}">View Registration Document</a></li>
        @endif
        @if($business->terms_and_conditions)
            <li><strong>Terms and Conditions:</strong> <a href="{{ asset('storage/' . $business->terms_and_conditions) }}">View Terms and Conditions</a></li>
        @endif
    </ul>
@endif
<p>If you have any questions, please contact support.</p> 