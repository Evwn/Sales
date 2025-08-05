<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Format phone number for M-PESA API
     * Converts local format (0111383064) to international format (254111383064)
     * 
     * @param string $phone
     * @return string
     */
    public static function formatForMpesa($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If it's already in international format (12 digits starting with 254)
        if (strlen($phone) === 12 && substr($phone, 0, 3) === '254') {
            return $phone;
        }
        
        // If it's in local format (10 digits starting with 0)
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            return '254' . substr($phone, 1);
        }
        
        // If it's 9 digits (without leading 0), add 254
        if (strlen($phone) === 9) {
            return '254' . $phone;
        }
        
        // If it's already 11 digits (might be 254 without leading 0)
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '25') {
            return '254' . substr($phone, 2);
        }
        
        // If it's already 11 digits starting with 0, convert to 254
        if (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
            return '254' . substr($phone, 1);
        }
        
        // If it's already 12 digits but doesn't start with 254, assume it's valid
        if (strlen($phone) === 12) {
            return $phone;
        }
        
        // If none of the above, return as is (will likely fail validation)
        return $phone;
    }
    
    /**
     * Validate phone number format
     * 
     * @param string $phone
     * @return bool
     */
    public static function isValid($phone)
    {
        $formatted = self::formatForMpesa($phone);
        
        // Check if it's a valid Kenyan phone number
        // Should be 12 digits starting with 254
        if (strlen($formatted) !== 12) {
            return false;
        }
        
        if (substr($formatted, 0, 3) !== '254') {
            return false;
        }
        
        // Check if the number after 254 is valid (should be 7-9 digits)
        $numberAfter254 = substr($formatted, 3);
        if (!preg_match('/^[0-9]{7,9}$/', $numberAfter254)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get validation error message
     * 
     * @param string $phone
     * @return string|null
     */
    public static function getValidationError($phone)
    {
        if (empty($phone)) {
            return 'Phone number is required';
        }
        
        $formatted = self::formatForMpesa($phone);
        
        if (strlen($formatted) !== 12) {
            return 'Phone number must be 10 digits (e.g., 0111383064) or 12 digits (e.g., 254111383064)';
        }
        
        if (substr($formatted, 0, 3) !== '254') {
            return 'Phone number must be a valid Kenyan number starting with 254';
        }
        
        $numberAfter254 = substr($formatted, 3);
        if (!preg_match('/^[0-9]{7,9}$/', $numberAfter254)) {
            return 'Invalid phone number format. Must be a valid Kenyan number';
        }
        
        return null;
    }
} 