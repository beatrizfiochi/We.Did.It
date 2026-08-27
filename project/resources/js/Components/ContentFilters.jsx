import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';

/**
 * Filtros por período e, opcionalmente, por categoria — usados nos ecrãs de
 * seleção de conteúdo da newsletter (News, Testimonials, Calendars).
 *
 * A filtragem em si acontece em cada ecrã, não aqui: o campo de data
 * comparado (created_at ou date) muda de um ecrã para o outro. Este
 * componente só guarda o estado dos filtros e desenha a UI.
 *
 * categories é opcional — quando omitido, a secção de categoria não aparece
 * (é o caso da agenda, que não tem category_id).
 */
export default function ContentFilters({
    categories,
    selectedCategories,
    onToggleCategory,
    onClearCategories,
    periodStart,
    periodEnd,
    onPeriodStartChange,
    onPeriodEndChange,
}) {
    return (
        <aside className="h-fit space-y-6 rounded-lg bg-white p-4 shadow-sm">
            <div>
                <div className="mb-2 font-semibold text-gray-900">Período</div>

                <div className="space-y-3">
                    <div>
                        <InputLabel htmlFor="period_start" value="De" />
                        <TextInput
                            id="period_start"
                            type="date"
                            className="mt-1 block w-full"
                            value={periodStart}
                            onChange={(e) => onPeriodStartChange(e.target.value)}
                        />
                    </div>

                    <div>
                        <InputLabel htmlFor="period_end" value="Até" />
                        <TextInput
                            id="period_end"
                            type="date"
                            className="mt-1 block w-full"
                            value={periodEnd}
                            onChange={(e) => onPeriodEndChange(e.target.value)}
                        />
                    </div>

                    {(periodStart || periodEnd) && (
                        <button
                            type="button"
                            className="text-sm font-semibold text-indigo-600 hover:text-indigo-900"
                            onClick={() => {
                                onPeriodStartChange('');
                                onPeriodEndChange('');
                            }}
                        >
                            Limpar período
                        </button>
                    )}
                </div>
            </div>

            {categories && (
                <div>
                    <div className="mb-2 font-semibold text-gray-900">Categoria</div>

                    <div className="space-y-2">
                        {categories.map((category) => (
                            <div className="flex items-center gap-2" key={category.id}>
                                <input
                                    className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    type="checkbox"
                                    id={`category-${category.id}`}
                                    checked={selectedCategories.includes(category.id)}
                                    onChange={() => onToggleCategory(category.id)}
                                />
                                <label className="text-sm text-gray-700" htmlFor={`category-${category.id}`}>
                                    {category.name}
                                </label>
                            </div>
                        ))}

                        {selectedCategories.length > 0 && (
                            <button
                                type="button"
                                className="text-sm font-semibold text-indigo-600 hover:text-indigo-900"
                                onClick={onClearCategories}
                            >
                                Limpar categoria
                            </button>
                        )}
                    </div>
                </div>
            )}
        </aside>
    );
}
