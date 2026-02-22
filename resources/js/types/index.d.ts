export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    role: string;
    locale: string;
}

export interface AuthCan {
    manage_settings: boolean;
    manage_users: boolean;
    create_edit: boolean;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        can: AuthCan;
    };
    locale: string;
    available_locales: string[];
};
