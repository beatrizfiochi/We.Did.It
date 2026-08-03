export default function FileInput({ className = '', previewUrl, ...props }) {
    return (
        <div className="space-y-3">
            <input
                {...props}
                type="file"
                className={
                    'block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 ' +
                    className
                }
            />

            {previewUrl && (
                <img
                    src={previewUrl}
                    alt="Pré-visualização do ficheiro selecionado"
                    className="h-40 w-full rounded-md object-cover"
                />
            )}
        </div>
    );
}
