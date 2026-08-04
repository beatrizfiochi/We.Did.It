// importar o css associado - Jessica ?
import './NewsForm.css'
import { Form } from "@inertiajs/react"
import { useRef, useState } from "react"

{/**Component takes children elements as the label titles, categoryList that fetches the existing array and fallbacktext */ }
export default function NewsForm({ formTitle = "Formulário", titleLabel = "Título", descriptionLabel = "Descrição", imageLabel = "Imagem", categoryLabel = "Categoria", categories = [] }) {

    const titleRef = useRef()
    const descriptionRef = useRef()
    const categoryRef = useRef()
    const imageRef = useRef()

    const [clientErrors, setClientErrors] = useState({})
    const [wasSuccessful, setWasSuccessful] = useState(false)
    const [imageUploaded, setImageUploaded] = useState(false) // flag for image upload/verification of size


    /* Cliente-side Form Validation before submiting the form(onSubmit)
    set error messages */
    function insertNews() {

        const title = titleRef.current.value.trim();
        const description = descriptionRef.current.value.trim();
        const category = categoryRef.current.value;
        const image = imageRef.current.files[0];


        const newErrors = {}

        if (title.length < 10 || title.length > 255) {
            newErrors.title = "O Título deve ter entre 10 e 255 caracteres."
        }

        if (description.length < 100 || description.length > 1050) {
            newErrors.description = "A Descrição deve ter entre 100 e 1050 caracteres."
        }


        // MAIS IMAGENS:  if (Object.keys(image).length > 9) {
        //     newErrors.image = "Selecione até 8 imagens."
        // }

        if (!image) {
            setImageUploaded(false)
        } else {
            // 2 MB max -- 1 KB -> 1024 bytes, 2MB = 2 bytes
            const maxSize = 2 * 1024 * 1024;

            if (image.size > maxSize) {
                newErrors.image = "A imagem deve ter no máximo 2 MB.";
            }
        }
        // check for the lenght of the object newErrors
        if (Object.keys(newErrors).length > 0) {
            setClientErrors(newErrors) // local state for displaying messages
            setWasSuccessful(false)
            return false // cancels the Inertia form submission
        } else {
            setClientErrors({})
            setWasSuccessful(true)
            return true // sends form submission 
        }


    }

    return (
        <div className="p-4">
            <div className="mx-auto">
                <h2 className="formTitle mt-5 mb-3">{formTitle}</h2>

                {/** inser all ids and htmlfor!!!!!!!!!!!  */}
                <Form className="formBody container shadow p-5"
                    method="POST"
                    action={route('news.store')}
                    noValidate // disables built-in html pop up messages
                    resetOnSuccess // resets visually all camps
                    onSuccess={() => { // resets errors to empty array
                        setClientErrors({});
                    }}
                    onBefore={insertNews} //before submiting calls the function for validation
                >

                    {/* children function needed for render/display */}
                    {({ processing }) => (
                        <>
                            <div className="">
                                <div>
                                    <label htmlFor="">{titleLabel}</label>
                                </div>
                                <input ref={titleRef} name="title" type="text" minLength={10} maxLength={255} placeholder="Insira o título da notícia" />

                                {/*ERROR MESSAGE - TITLE SIZE  */}
                                {clientErrors.title && (
                                    <div className="mt-1 text-sm text-red-500">
                                        {clientErrors.title}
                                    </div>
                                )}

                            </div>


                            <div className="mt-3">
                                <div>
                                    <label className="" htmlFor="">{descriptionLabel}</label>
                                </div>
                                <textarea ref={descriptionRef} name="description" minLength={100} maxLength={1050} rows={6} cols={40} placeholder=" Descreva a notícia...">
                                </textarea>   {/*verficiar se 1050 é muito ou pouco, admin pode editar anyways */}

                                {/* ERROR MESSAGE - DESCRIPTIONLabeldescriptionLabel SIZE  */}
                                {clientErrors.description && (
                                    <div className="mt-1 text-sm text-red-500">
                                        {clientErrors.description}
                                    </div>
                                )}
                            </div>


                            {/* * iterate this over the existing categories*/}
                            <div className="mt-3">
                                <div>
                                    <label htmlFor="">{categoryLabel}</label>
                                </div>
                                <select ref={categoryRef} name="category_id" id="">
                                    {/* Value for option "nenhuma" is empty string so NewsFormController passes it down as null */}
                                    <option value="" default>Nenhuma</option>
                                    {categories.map((item) =>
                                        <option key={item.id} value={item.id}>{item.name}</option>
                                    )}
                                </select>
                            </div>

                            {/* Error Message - no Categories available */}
                            {categories.length === 0 && <p className="mt-1 text-sm text-red-500">Não existem categorias disponíveis.</p>}

                            <div className="mt-3">
                                <div>
                                    <label htmlFor="">{imageLabel}</label>
                                </div>
                                <input ref={imageRef} name="image" type="file" accept="image/jpg, image/jpeg, image/png" />
                            </div>
                            {/* ERROR MESSAGE - IMAGE SIZE  */}
                            {clientErrors.image && (
                                <div className="mt-1 text-sm text-red-500">
                                    {clientErrors.image}
                                </div>
                            )}


                            <div className="mt-4">
                                <button type="submit" className="btn btn-primary" disabled={processing} >Submeter</button> {/** add onSubmit={rota do post} */}
                            </div>

                            {/* Success message onSubmit */}
                            {wasSuccessful && (
                                <div className="mt-1 text-sm bg-success">
                                    Notícia enviada com sucesso.
                                </div>
                            )}


                        </>
                    )}

                </Form>
            </div>

        </div >

    )

} 
