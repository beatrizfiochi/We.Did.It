import GeneralForm from "@/Components/Form/GeneralForm"
import { Head } from "@inertiajs/react"
import { useState } from "react"
import PublicLayout from "@/Layouts/PublicLayout"


export default function InsertForm({ categories }) {

    // campos do formulário, na mesma forma que o InsertForm dos testemunhos usa
    const fields =
        [
            { name: 'title', label: 'Título', type: 'text' },
            { name: 'description', label: 'Descrição', type: 'textarea' },
            { name: 'category_id', label: 'Categoria', type: 'select' },
            { name: 'image', label: 'Imagem', type: 'file' },
        ]

    const [clientErrors, setClientErrors] = useState({})

    // Chamada no onBefore do <Form>: recebe o FormData que o GeneralForm obtém
    // do getFormData(). Devolver false cancela a submissão do Inertia.
    //
    // As regras espelham o StoreNewsRequest. Quando divergem, o cliente deixa
    // passar algo que o servidor recusa (ou ao contrário) e o utilizador fica
    // sem perceber porquê.
    function insertNews(dataForm) {

        const title = dataForm.get('title')
        const description = dataForm.get('description')
        const image = dataForm.get('image')

        const image_rights = dataForm.get('image_rights')
        const terms_conditions = dataForm.get('terms_conditions')

        const newErrors = {}

        if (title.length < 5 || title.length > 255) {
            newErrors['title'] = "O Título deve ter entre 5 e 255 caracteres."
        }

        if (description.length < 100 || description.length > 1050) {
            newErrors['description'] = "A Descrição deve ter entre 100 e 1050 caracteres."
        }

        if (image && image.size > 0) {
            // igual ao max:2048 do StoreNewsRequest
            if (image.size > 2 * 1024 * 1024) {
                newErrors['image'] = "A imagem deve ter no máximo 2 MB."
            }

            if (!image_rights) {
                newErrors['image_rights'] = "É necessário autorizar a utilização da imagem."
            }
        }

        if (!terms_conditions) {
            newErrors['terms_conditions'] = "É necessário aceitar a Política de Privacidade."
        }

        if (Object.keys(newErrors).length > 0) {
            setClientErrors(newErrors)
            return false
        }

        setClientErrors({})
        return true
    }

    return (
        <PublicLayout>
            <Head title="Inserir uma notícia" />

            <div>
                <GeneralForm
                    formTitle="Inserir uma Notícia"
                    formMethod="POST"
                    formAction={route('news.store')}
                    fields={fields}
                    clientErrors={clientErrors}
                    categoryList={categories}
                    submitFunction={insertNews}
                />
            </div>
        </PublicLayout>
    );
}
