const statusStyles = {
    received: 'bg-yellow-100 text-yellow-800',
    accepted: 'bg-green-100 text-green-800',
    refused: 'bg-red-100 text-red-800',
};

const statusLabels = {
    received: 'Recebido',
    accepted: 'Aceite',
    refused: 'Recusado',
};

export default function StatusBadge({ status }) {
    return (
        <span
            className={
                'inline-flex rounded-full px-3 py-1 text-xs font-semibold ' +
                (statusStyles[status] ?? 'bg-gray-100 text-gray-800')
            }
        >
            {statusLabels[status] ?? status}
        </span>
    );
}
