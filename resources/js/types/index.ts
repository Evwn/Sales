export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
    role: 'admin' | 'owner' | 'seller';
    created_at: string;
    updated_at: string;
}

export interface SharedData {
    auth: {
        user: User;
    };
}

export interface BreadcrumbItemType {
    title: string;
    href?: string;
}

export interface Business {
    id: number;
    name: string;
    owner_id: number;
    created_at: string;
    updated_at: string;
}

export interface Branch {
    id: number;
    name: string;
    business_id: number;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    name: string;
    description: string | null;
    price: number;
    business_id: number;
    created_at: string;
    updated_at: string;
}

export interface InventoryItem {
    id: number;
    name: string;
    description: string | null;
    brand: string | null;
    model: string | null;
    sku: string | null;
    barcode: string | null;
    upc: string | null;
    ean: string | null;
    isbn: string | null;
    mpn: string | null;
    unit: string | null;
    unit_value: number | null;
    created_by: number;
    last_updated_by: number;
    created_at: string;
    updated_at: string;
}

export interface Sale {
    id: number;
    business_id: number;
    branch_id: number;
    seller_id: number;
    total_amount: number;
    created_at: string;
    updated_at: string;
}

export interface Discount {
    id: number;
    business_id: number;
    name: string;
    description: string | null;
    type: 'percentage' | 'fixed';
    value: number;
    start_date: string;
    end_date: string | null;
    created_at: string;
    updated_at: string;
}

export interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: any;
} 