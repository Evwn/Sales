export interface BreadcrumbItemType {
    title: string;
    href?: string;
    active?: boolean;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: any;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
}

export interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: any;
}

export interface Quote {
    message: string;
    author: string;
}

export interface SharedData extends PageProps {
    name: string;
    quote: Quote;
    sidebarOpen: boolean;
} 