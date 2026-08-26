import DataTable from "@/Components/DataTable";
import FlashMessage from "@/Components/FlashMessage";
import Modal from "@/Components/Modal";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useState } from "react";
import { Head, router } from "@inertiajs/react";



// tabela com as noticias todas e botao para ver --> modal
export default function Index({ news, categories }) {


    // variable that holds the labels to be rendered in <DataTable/> according to keys from the news table
    const columns = [
        { key: 'title', label: 'Título', render: (row) => row.title },
        //{ key: 'description', label: 'Descrição', render: (row) => row.description },
        { key: 'category', label: 'Categoria', render: (row) => row.category?.name ?? "N/a" },
        {
            key: 'status',
            label: 'Estado',
            render: (row) => statusLabels[row.status] ?? row.status
        },
        {
            key: 'actions',
            label: 'Ações',
            render: (row) => (
                <div className="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        className="text-sm font-semibold text-green-600 hover:text-green-900 disabled:cursor-not-allowed disabled:text-gray-400"
                        disabled={row.status === 'accepted'}
                        onClick={() => router.patch(route('admin.news.approve', row.id), {}, { preserveScroll: true })}
                    >
                        Aprovar
                    </button>
                    <button
                        type="button"
                        className="text-sm font-semibold text-red-600 hover:text-red-900 disabled:cursor-not-allowed disabled:text-gray-400"
                        disabled={row.status === 'refused'}
                        onClick={() => router.patch(route('admin.news.refuse', row.id), {}, { preserveScroll: true })}
                    >
                        Recusar
                    </button>

                </div>
            ),
        },
        {
            key: 'view', label: '', render: (row) => (
                <button
                    type="button"
                    className="text-sm font-semibold text-indigo-600 hover:text-indigo-900"
                    onClick={() => handleViewNews(row)}
                >
                    Ver
                </button>
            )
        }
    ]


    // labels for status
    const statusLabels = {
        received: "Recebido",
        accepted: "Aprovado",
        refused: "Recusado",
    };


    // variables for search filter by category 
    const [selectedCategories, setSelectedCategories] = useState([]);
    const [searchTerm, setSearchTerm] = useState("");
    const [isCategoryOpen, setIsCategoryOpen] = useState(true); // controls dropdown open/closed


    // handled by onChange
    const toggleCategory = (categoryId) => {
        setSelectedCategories((prev) =>
            // uncheck: removes filter, check: adds the filter
            prev.includes(categoryId) ? prev.filter((id) => id !== categoryId) : [...prev, categoryId]
        );
    }

    // what is shown in the table is according to the filtered news ( BY Status)
    const filteredNews = news.filter((item) => {

        // use the status in portuguese coming from the variable as the status column for the news, else use original one
        // se um estado novo aparecer sem tradução, mostra-se o valor cru em vez
        // de a linha desaparecer da tabela
        const statusLabel = statusLabels[item.status] ?? item.status;

        // search permits searching status in portuguese or original form from DB (in english)
        const matchesStatus = statusLabel.toLowerCase().includes(searchTerm.toLowerCase());

        // what is shown in the table is according to the filtered news ( By category)
        const matchesCategory = selectedCategories.length === 0 || selectedCategories.includes(item.category_id);

        return matchesStatus && matchesCategory;
    });

    // when user clears category filter search
    const clearCategories = () => setSelectedCategories([])


    // for the VIEW Modal
    const [selectedNews, setSelectedNews] = useState(null)
    const [showViewModal, setShowViewModal] = useState(false)
    const handleViewNews = (row) => {
        setSelectedNews(row);
        setShowViewModal(true); // shows the row selected inside the modal
    };

    const closeViewModal = () => {
        setShowViewModal(false);
        setSelectedNews(null); // turns selected view as null, closes modal
    }





    // for the EDIT Modal
    const [editingNews, setEditingNews] = useState(null)
    const [showEditModal, setShowEditModal] = useState(false)
    const [editErrors, setEditErrors] = useState({})
    const handleEditClick = (row) => {
        setEditingNews({ ...row });
        setEditErrors({});
        setShowEditModal(true);
    };

    const handleSaveEdit = () => {
        router.post(
            route('admin.news.update', editingNews.id),
            {
                _method: 'put',
                title: editingNews.title,
                description: editingNews.description,
                category_id: editingNews.category_id,
                image: editingNews.imageFile ?? undefined, // if there isnt a new imageFile it stays undefined, and doesnt update image field
            },
            {
                forceFormData: true, // because a file can be input
                onError: (errors) => {
                    console.log(errors);
                    setEditErrors(errors);
                },

                onSuccess: () => {
                    closeEditModal();
                },
            }
        );
    };


    const closeEditModal = () => {
        setShowEditModal(false);
        setEditingNews(null);
    };





    return (

        <AuthenticatedLayout header="Notícias">
            <Head title="Notícias" />

            <FlashMessage />

            <div className="grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)]">

                <aside className="rounded-lg bg-white p-4 shadow-sm">
                    <div className="mb-2 font-semibold text-gray-900">Filtrar por</div>

                    <div
                        className="flex cursor-pointer items-center justify-between text-sm font-medium text-gray-700"
                        onClick={() => setIsCategoryOpen((prev) => !prev)}
                    >
                        <span>Categoria</span>
                        <span aria-hidden="true">{isCategoryOpen ? '⌃' : '⌄'}</span>
                    </div>

                    {isCategoryOpen && (
                        <div className="mt-2 space-y-2">
                            {categories.map((item) => (
                                <div className="flex items-center gap-2" key={item.id}>
                                    <input
                                        className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        type="checkbox"
                                        value={item.id}
                                        id={`category-${item.id}`}
                                        checked={selectedCategories.includes(item.id)}
                                        onChange={() => toggleCategory(item.id)}
                                    />
                                    <label className="text-sm text-gray-700" htmlFor={`category-${item.id}`}>
                                        {item.name}
                                    </label>
                                </div>
                            ))}

                            <button
                                type="button"
                                className="text-sm font-semibold text-indigo-600 hover:text-indigo-900"
                                onClick={clearCategories}
                            >
                                Limpar
                            </button>
                        </div>
                    )}
                </aside>


                <section className="min-w-0 space-y-4">
                    <div className="max-w-sm lg:ml-auto">
                        <input className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            type="search" placeholder="Pesquisar por estado..."
                            aria-label="Search"
                            value={searchTerm}
                            onChange={(event) => setSearchTerm(event.target.value)}
                        />
                    </div>

                    <DataTable
                        columns={columns}
                        rows={filteredNews} // shows according to what is set in the filter areas
                    />
                </section>

            </div>


            {/* Modal for viewing each individual news */}
            <Modal show={showViewModal} onClose={closeViewModal} maxWidth="lg">
                {selectedNews && (
                    <div className="p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">
                            {selectedNews.title}
                        </h2>

                        <p className="text-sm text-gray-500 mb-2">
                            <strong>Categoria:</strong> {selectedNews.category?.name ?? "N/a"}
                        </p>

                        <p className="text-sm text-gray-500 mb-4">
                            <strong>Estado:</strong> {selectedNews.status}
                        </p>

                        <p className="text-gray-700">
                            {selectedNews.description}
                        </p>

                        {selectedNews.image && (
                            <img src={`/storage/${selectedNews.image}`}
                                alt={selectedNews.title}
                                className="w-full h-auto rounded-lg mb-4" />
                        )}

                        <div className="mt-6 flex flex-wrap justify-end gap-3">
                            <button
                                type="button"
                                className="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                                onClick={() => {
                                    handleEditClick(selectedNews);
                                    closeViewModal();
                                }}
                            >
                                Editar
                            </button>

                            <button
                                type="button"
                                className="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50"
                                onClick={closeViewModal}
                            >
                                Fechar
                            </button>
                        </div>
                    </div>
                )}
            </Modal>



            {/* Modal for editing with inputs*/}
            <Modal show={showEditModal} onClose={closeEditModal} maxWidth="lg">
                {editingNews && (
                    <div className="p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">
                            Editar Notícia
                        </h2>

                        <div className="mb-3">
                            <label className="block text-sm font-medium text-gray-700">Título</label>
                            <input
                                type="text"
                                className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ${editErrors.title ? 'border-red-500' : ''}`}
                                value={editingNews.title}
                                onChange={(e) =>
                                    setEditingNews({ ...editingNews, title: e.target.value })
                                }
                            />

                            {editErrors.title && (
                                <div className="mt-2 text-sm text-red-600">
                                    {editErrors.title}
                                </div>
                            )}
                        </div>

                        <div className="mb-3">
                            <label className="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea
                                className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ${editErrors.description ? 'border-red-500' : ''}`}
                                rows={4}
                                value={editingNews.description}
                                onChange={(e) =>
                                    setEditingNews({ ...editingNews, description: e.target.value })
                                }
                            />

                            {editErrors.description && (
                                <div className="mt-2 text-sm text-red-600">
                                    {editErrors.description}
                                </div>
                            )}
                        </div>

                        <div className="mb-3">
                            <label className="block text-sm font-medium text-gray-700">Categoria</label>
                            <select
                                className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 ${editErrors.category_id ? 'border-red-500' : ''}`}
                                value={editingNews.category_id ?? ""}
                                onChange={(e) =>
                                    setEditingNews({ ...editingNews, category_id: e.target.value || null })
                                }
                            >
                                <option value="">Nenhuma</option>
                                {categories.map((cat) => (
                                    <option key={cat.id} value={cat.id}>
                                        {cat.name}
                                    </option>
                                ))}
                            </select>

                            {editErrors.category_id && (
                                <div className="mt-2 text-sm text-red-600">
                                    {editErrors.category_id}
                                </div>
                            )}
                        </div>

                        <div className="mb-3">
                            <label className="block text-sm font-medium text-gray-700">Imagem</label>

                            <input
                                type="file"
                                className={`mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-gray-800 file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-widest file:text-white hover:file:bg-gray-700 ${editErrors.image ? 'text-red-600' : ''}`}
                                accept="image/jpg,image/jpeg,image/png"
                                onChange={(e) => {
                                    const file = e.target.files[0];

                                    if (!file) {
                                        return
                                    }

                                    // front-end validation for choosing image with 2mb
                                    // igual ao max:5120 do UpdateNewsRequest
                                    const maxSizeBytes = 5 * 1024 * 1024 // 5MB

                                    if (file.size > maxSizeBytes) {
                                        setEditErrors(prev => ({
                                            ...prev,
                                            image: 'O ficheiro deve ter no máximo 5MB.'
                                        }));

                                        // image value stays empty so it doesnt get uploaded
                                        e.target.value = '';
                                        return; // stops here
                                    }


                                    // Valid file — clear image error
                                    setEditErrors(prev => ({
                                        ...prev,
                                        image: undefined
                                    }));

                                    setEditingNews(prev => ({
                                        ...prev,
                                        imageFile: file
                                    }));
                                }}
                            />

                            {/* Show a preview: new file if picked, otherwise the existing stored image */}
                            {editingNews.imageFile ? (
                                <img
                                    src={URL.createObjectURL(editingNews.imageFile)}
                                    alt="Preview"
                                    className="w-32 h-auto rounded-lg mt-2"
                                />
                            ) : editingNews.image ? (
                                <img
                                    src={`/storage/${editingNews.image}`}
                                    alt="Imagem atual"
                                    className="w-32 h-auto rounded-lg mt-2"
                                />
                            ) : null}

                            {editErrors.image && (
                                <div className="mt-2 text-sm text-red-600">
                                    {editErrors.image}
                                </div>
                            )}
                        </div>


                        <div className="mt-4 flex flex-wrap justify-end gap-3">
                            <button
                                type="button"
                                className="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50"
                                onClick={closeEditModal}
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                className="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700"
                                onClick={handleSaveEdit}
                            >
                                Guardar
                            </button>
                        </div>
                    </div>
                )}
            </Modal>



        </AuthenticatedLayout>
    )
}
