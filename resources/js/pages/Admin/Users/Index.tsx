import {
    Head,
} from '@inertiajs/react';

import AdminLayout from '@/layouts/AdminLayout';

type Role = {
    id: number;
    name: string;
};

type UserRow = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    lock_version: number;
    roles: Role[];
};

type UsersPage = {
    data: UserRow[];
};

type Props = {
    users: UsersPage;
};

export default function Index({
    users,
}: Props) {
    return (
        <>
            <Head title="Users" />

            <AdminLayout>
                <section>
                    <h1 className="text-3xl font-bold text-cim-navy">
                        Users
                    </h1>

                    <p className="mt-2 text-sm text-muted-foreground">
                        Read-only management surface Phase 2.
                        Mutation tetap tersedia melalui endpoint
                        terotorisasi dan akan diperluas pada fase CMS.
                    </p>

                    <div className="mt-6 overflow-x-auto rounded-xl border bg-background">
                        <table className="w-full text-left text-sm">
                            <thead className="border-b bg-muted/50">
                                <tr>
                                    <th className="px-4 py-3">
                                        User
                                    </th>

                                    <th className="px-4 py-3">
                                        Status
                                    </th>

                                    <th className="px-4 py-3">
                                        Roles
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                {users.data.map((user) => (
                                    <tr
                                        key={user.id}
                                        className="border-b last:border-0"
                                    >
                                        <td className="px-4 py-3">
                                            <p className="font-medium">
                                                {user.name}
                                            </p>

                                            <p className="text-muted-foreground">
                                                {user.email}
                                            </p>
                                        </td>

                                        <td className="px-4 py-3">
                                            {user.is_active
                                                ? 'Active'
                                                : 'Inactive'}
                                        </td>

                                        <td className="px-4 py-3">
                                            {user.roles
                                                .map(
                                                    (role) =>
                                                        role.name,
                                                )
                                                .join(', ')
                                                || '—'}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>
            </AdminLayout>
        </>
    );
}