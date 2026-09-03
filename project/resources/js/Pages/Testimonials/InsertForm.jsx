import GeneralForm from "@/Components/Form/GeneralForm"
import { Head } from "@inertiajs/react"
import { useState } from "react"
import PublicLayout from "@/Layouts/PublicLayout"


export default function InsertForm({ categories }) {

    // Variables that hold arrays with the labels and input type and name 
    const fields =
        [
            { name: 'name', label: 'Nome', type: 'text' },
            { name: 'email', label: 'Email', type: 'email' },
            { name: 'title', label: 'Título', type: 'text' },
            { name: 'description', label: 'Descrição', type: 'textarea' },
            { name: 'category_id', label: 'Categoria', type: 'select' },
            { name: 'images', label: 'Imagens (até 3)', type: 'file' },
        ]



    const [clientErrors, setClientErrors] = useState({})
    const [imageUploaded, setImageUploaded] = useState(false)



    // Chamada no onBefore do <Form>: recebe o FormData que o GeneralForm obtém
    // do getFormData(). Devolver false cancela a submissão do Inertia — já não
    // há event.preventDefault(), porque não há evento de DOM.
    function insertTestimonial(dataForm) {

        // variables coming from the form input
        const name = dataForm.get('name')
        const title = dataForm.get('title')
        const description = dataForm.get('description')
        // getAll e não get: o campo é images[] e pode trazer até 3. Um input de
        // ficheiro vazio ainda submete uma entrada de tamanho 0, daí o filtro
        const images = dataForm.getAll('images[]').filter((file) => file.size > 0)

        const image_rights = dataForm.get('image_rights'); // to be checked in case of uploaded image
        const terms_conditions = dataForm.get('terms_conditions');

        // object that holds errors according to what's inside here [''] -> the name 
        const newErrors = {}

        // As regras abaixo espelham o StoreTestimonialRequest. Quando divergem,
        // o cliente deixa passar algo que o servidor recusa (ou ao contrário) e
        // o utilizador fica sem perceber porquê.
        if (name.length > 255) {
            newErrors['name'] = "O nome deve ter no máximo 255 caracteres."
        }

        // o email é validado no servidor (required|email): qualquer pessoa pode
        // submeter, não só emails institucionais

        // title validation
        if (title.length < 5 || title.length > 255) {
            newErrors['title'] = "O Título deve ter entre 5 e 255 caracteres."
        }

        // description validation
        if (description.length < 100 || description.length > 1050) {
            newErrors['description'] = "A Descrição deve ter entre 100 e 1050 caracteres."
        }

        // if images were picked, needs to check image rights checkmark
        if (images.length > 0) {
            // os três limites espelham o StoreTestimonialRequest: max:3 no
            // conjunto, max:5120 (5 MB) por ficheiro
            if (images.length > 3) {
                newErrors['images'] = "Podes enviar no máximo 3 imagens."
            } else if (images.some((file) => file.size > 5 * 1024 * 1024)) {
                newErrors['images'] = "Cada imagem deve ter no máximo 5 MB."
            }

            if (!image_rights) {
                newErrors['image_rights'] = "É necessário autorizar a utilização da imagem.";
            }
        } else {
            // no image was picked
            setImageUploaded(false)
        }

        // if terms and conditions isnt checked, there is error message
        if (!terms_conditions) {
            newErrors['terms_conditions'] = "É necessário aceitar a Política de Privacidade."
        }

        if (Object.keys(newErrors).length > 0) {
            setClientErrors(newErrors)
            return false

        } else {
            setClientErrors({})
            return true // sends form now that it's valid

        }
    }



    return (
        <PublicLayout>
            <Head title="Adicionar testemunho" />

            <div>
                <GeneralForm
                    formTitle="Adicionar Testemunho"
                    formMethod="POST"
                    formAction={route('testimonials.store')}
                    fields={fields}
                    clientErrors={clientErrors}
                    categoryList={categories}
                    submitFunction={insertTestimonial}
                />
            </div>
        </PublicLayout>
    );
}