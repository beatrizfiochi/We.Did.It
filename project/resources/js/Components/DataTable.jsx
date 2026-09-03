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
            <div className="hidden overflow-x-auto sm:block">
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

            <div className="divide-y divide-gray-200 sm:hidden">
                {rows.map((row, rowIndex) => (
                    <article key={row.id ?? rowIndex} className="space-y-3 p-4">
                        {columns.map((column) => {
                            const content = column.render
                                ? column.render(row)
                                : row[column.key];

                            if (!column.label || column.key === 'actions') {
                                return (
                                    <div key={column.key} className="text-sm text-gray-800">
                                        {content}
                                    </div>
                                );
                            }

                            return (
                                <dl key={column.key}>
                                    <div className="grid grid-cols-[7rem_minmax(0,1fr)] gap-3 text-sm">
                                        <dt className="font-semibold uppercase tracking-wide text-gray-500">
                                            {column.label}
                                        </dt>

                                        <dd className="min-w-0 break-words text-gray-800">
                                            {content}
                                        </dd>
                                    </div>
                                </dl>
                            );
                        })}
                    </article>
                ))}
            </div>
        </div>
    );
}
