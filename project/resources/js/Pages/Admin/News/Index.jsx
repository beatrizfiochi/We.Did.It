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
                <div className="d-flex gap-2">
                    <button
                        type="button"
                        className="btn btn-success btn-sm"
                        disabled={row.status === 'accepted'}
                        onClick={() => router.patch(route('admin.news.approve', row.id), {}, { preserveScroll: true })}
                    >
                        Aprovar
                    </button>
                    <button
                        type="button"
                        className="btn btn-danger btn-sm"
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
                    className="btn btn-outline-secondary btn-sm"
                    onClick={() => handleViewNews(row)}
                >
                    <i className="bi bi-eye"></i> Ver
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

            <div className="row ">

                <div className=" mt-5 col-2 py-4 pe-4">
                    <div className="fw-bold mb-2">Filtrar por</div>

                    <div
                        className="d-flex justify-content-between align-items-center"
                        style={{ cursor: 'pointer' }}
                        onClick={() => setIsCategoryOpen((prev) => !prev)}
                    >
                        <span>Categoria</span>
                        <i className={`bi ${isCategoryOpen ? 'bi-chevron-up' : 'bi-chevron-down'}`}></i>
                    </div>

                    {isCategoryOpen && (
                        <div className="mt-2">
                            {categories.map((item) => (
                                <div className="form-check" key={item.id}>
                                    <input
                                        className="form-check-input"
                                        type="checkbox"
                                        value={item.id}
                                        id={`category-${item.id}`}
                                        checked={selectedCategories.includes(item.id)}
                                        onChange={() => toggleCategory(item.id)}
                                    />
                                    <label className="form-check-label" htmlFor={`category-${item.id}`}>
                                        {item.name}
                                    </label>
                                </div>
                            ))}

                            <button
                                type="button"
                                className="btn btn-link btn-sm p-0 text-decoration-none"
                                onClick={clearCategories}
                            >
                                Limpar
                            </button>
                        </div>
                    )}
                </div>


                <div className="col-10 p-4">
                    <div className="col-3 mb-2 ml-auto">
                        <input className="form-control mr-sm-2"
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
                </div>

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

                        <div className="mt-6 flex justify-end">
                            <button
                                type="button"
                                className="btn btn-primary btn-sm mr-3"
                                onClick={() => {
                                    handleEditClick(selectedNews);
                                    closeViewModal();
                                }}
                            >
                                Editar
                            </button>

                            <button
                                type="button"
                                className="btn btn-secondary btn-sm"
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
                            <label className="form-label">Título</label>
                            <input
                                type="text"
                                className={`form-control ${editErrors.title ? 'is-invalid' : ''}`}
                                value={editingNews.title}
                                onChange={(e) =>
                                    setEditingNews({ ...editingNews, title: e.target.value })
                                }
                            />

                            {editErrors.title && (
                                <div className="invalid-feedback">
                                    {editErrors.title}
                                </div>
                            )}
                        </div>

                        <div className="mb-3">
                            <label className="form-label">Descrição</label>
                            <textarea
                                className={`form-control ${editErrors.description ? 'is-invalid' : ''}`}
                                rows={4}
                                value={editingNews.description}
                                onChange={(e) =>
                                    setEditingNews({ ...editingNews, description: e.target.value })
                                }
                            />

                            {editErrors.description && (
                                <div className="invalid-feedback">
                                    {editErrors.description}
                                </div>
                            )}
                        </div>

                        <div className="mb-3">
                            <label className="form-label">Categoria</label>
                            <select
                                className={`form-select ${editErrors.category_id ? 'is-invalid' : ''}`}
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
                                <div className="invalid-feedback">
                                    {editErrors.category_id}
                                </div>
                            )}
                        </div>

                        <div className="mb-3">
                            <label className="form-label">Imagem</label>

                            <input
                                type="file"
                                className={editErrors.image ? 'is-invalid' : ''}
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
                                <div className="invalid-feedback">
                                    {editErrors.image}
                                </div>
                            )}
                        </div>


                        <div className="d-flex justify-content-end gap-2 mt-4">
                            <button
                                type="button"
                                className="btn btn-secondary btn-sm"
                                onClick={closeEditModal}
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                className="btn btn-primary btn-sm"
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
