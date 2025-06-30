<?php

use App\Http\Controllers\AIAssistantController;
use App\Http\Middleware\AIQueryScope;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\SellerChatController;

Route::middleware(['auth:sanctum', AIQueryScope::class])->post('/ai-assistant', [AIAssistantController::class, 'handle']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/ai-chat/history', [AIChatController::class, 'history']);
    Route::post('/ai-chat/send', [AIChatController::class, 'send']);
    // Seller chat endpoints
    Route::get('/seller-chat/{sellerId}/history', [SellerChatController::class, 'history']);
    Route::post('/seller-chat/{sellerId}/send', [SellerChatController::class, 'send']);
}); 