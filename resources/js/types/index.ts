export type AuthUser = {
    id: number;
    name: string;
    email: string;
};

export type NavigationItem = {
    label: string;
    href: string;
};

export type SharedPageProps = {
    [key: string]: unknown;

    app: {
        name: string;
        environment: string;
    };

    auth: {
        user: AuthUser | null;
        permissions: string[];
        is_super_admin: boolean;
    };

    navigation: NavigationItem[];

    flash: {
        status: string | null;
        success: string | null;
    };
};