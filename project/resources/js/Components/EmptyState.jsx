export default function EmptyState({
    title = 'Ainda não há dados para mostrar',
    description = 'Quando houver registos, eles vão aparecer aqui.',
    action = null,
}) {
    return (
        <div className="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-10 text-center">
            <h3 className="text-base font-semibold text-gray-900">
                {title}
            </h3>

            <p className="mx-auto mt-2 max-w-md text-sm text-gray-600">
                {description}
            </p>

            {action && (
                <div className="mt-6">
                    {action}
                </div>
            )}
        </div>
    );
}
