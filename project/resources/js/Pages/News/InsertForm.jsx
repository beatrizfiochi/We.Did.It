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
            { name: 'event_start_date', label: 'Data do evento', type: 'date' },
            { name: 'event_end_date', label: 'Data de fim (só se durou mais do que um dia)', type: 'date' },
            { name: 'category_id', label: 'Categoria', type: 'select' },
            { name: 'images', label: 'Imagens (até 3)', type: 'file' },
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

        const inicio = dataForm.get('event_start_date')
        const fim = dataForm.get('event_end_date')
        const hoje = new Date().toISOString().slice(0, 10)

        // getAll e não get: o campo é images[] e pode trazer até 3. Um input de
        // ficheiro vazio ainda submete uma entrada de tamanho 0, daí o filtro
        const images = dataForm.getAll('images[]').filter((file) => file.size > 0)

        const image_rights = dataForm.get('image_rights')
        const terms_conditions = dataForm.get('terms_conditions')

        const newErrors = {}

        if (title.length < 5 || title.length > 255) {
            newErrors['title'] = "O Título deve ter entre 5 e 255 caracteres."
        }

        if (description.length < 100 || description.length > 1050) {
            newErrors['description'] = "A Descrição deve ter entre 100 e 1050 caracteres."
        }

        if (!inicio) {
            newErrors['event_start_date'] = "A data do evento é obrigatória."
        } else if (inicio > hoje) {
            newErrors['event_start_date'] = "A data do evento não pode ser no futuro."
        } else if (fim && fim < inicio) {
            newErrors['event_end_date'] = "A data de fim não pode ser anterior à data do evento."
        }

        if (images.length > 0) {
            // os três limites espelham o StoreNewsRequest: max:3 no conjunto,
            // max:5120 (5 MB) por ficheiro
            if (images.length > 3) {
                newErrors['images'] = "Podes enviar no máximo 3 imagens."
            } else if (images.some((file) => file.size > 5 * 1024 * 1024)) {
                newErrors['images'] = "Cada imagem deve ter no máximo 5 MB."
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
            <Head title="Adicionar notícia" />

            <div>
                <GeneralForm
                    formTitle="Adicionar Notícia"
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
