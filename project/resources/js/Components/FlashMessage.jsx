import { usePage } from '@inertiajs/react';

export default function FlashMessage() {
    const flash = usePage().props.flash;

    if (!flash?.success && !flash?.error) {
        return null;
    }

    return (
        <div className="space-y-3">
            {flash.success && (
                <div className="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                    {flash.success}
                </div>
            )}

            {flash.error && (
                <div className="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    {flash.error}
                </div>
            )}
        </div>
    );
}
