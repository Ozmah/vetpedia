import { Link, router } from '@inertiajs/react';
import { LogOut, Settings } from 'lucide-react';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuLinkItem,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { logout } from '@/routes';
import { edit } from '@/routes/user-profile';
import type { User } from '@/types';

type Props = {
    user: User;
    onNavigate?: () => void;
};

export function UserMenuContent({ user, onNavigate }: Props) {
    const handleLogout = () => {
        onNavigate?.();
        router.flushAll();
    };

    return (
        <>
            <DropdownMenuGroup>
                <DropdownMenuLabel className="p-0 font-normal">
                    <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                        <UserInfo user={user} showEmail={true} />
                    </div>
                </DropdownMenuLabel>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuLinkItem
                    render={
                        <Link
                            className="block w-full cursor-pointer"
                            href={edit()}
                            prefetch
                            onClick={onNavigate}
                        />
                    }
                >
                    <Settings className="mr-2" />
                    Settings
                </DropdownMenuLinkItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                nativeButton
                render={
                    <Link
                        className="block w-full cursor-pointer"
                        href={logout()}
                        as="button"
                        onClick={handleLogout}
                        data-test="logout-button"
                    />
                }
            >
                <LogOut className="mr-2" />
                Log out
            </DropdownMenuItem>
        </>
    );
}
