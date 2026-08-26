import EmptyState from '@/Components/EmptyState';

export default function DataTable({
    columns = [],
    rows = [],
    emptyTitle = 'Ainda não há dados para mostrar',
    emptyDescription = 'Quando houver registos, eles vão aparecer aqui.',
}) {
    if (rows.length === 0) {
        return (
            <EmptyState
                title={emptyTitle}
                description={emptyDescription}
            />
        );
    }

    return (
        <div className="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            {columns.map((column) => (
                                <th
                                    key={column.key}
                                    scope="col"
                                    className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 sm:px-6"
                                >
                                    {column.label}
                                </th>
                            ))}
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-gray-200 bg-white">
                        {rows.map((row, rowIndex) => (
                            <tr key={row.id ?? rowIndex}>
                                {columns.map((column) => (
                                    <td
                                        key={column.key}
                                        className="max-w-xs break-words px-4 py-4 align-top text-sm text-gray-700 sm:px-6"
                                    >
                                        {column.render
                                            ? column.render(row)
                                            : row[column.key]}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
