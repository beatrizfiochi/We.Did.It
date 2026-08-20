// importar o css associado - Jessica ?
import './NewsForm.css'
import { Form } from "@inertiajs/react"
import { useRef, useState } from "react"

{/**Component takes children elements as the label titles, categoryList that fetches the existing array and fallbacktext */ }
export default function NewsForm({ formTitle = "Formulário", titleLabel = "Título", descriptionLabel = "Descrição", imageLabel = "Imagem", categoryLabel = "Categoria", categories = [] }) {


    // useRef() creates a box that holds a value across re-renders, and stays the same box for the entire lifetime of the component, comes from ref={} attribute in the inputs of the form
    const titleRef = useRef()
    const descriptionRef = useRef()
    const categoryRef = useRef()
    const imageRef = useRef()
    const image_rights_Ref = useRef()
    const terms_and_conditions_Ref = useRef()

    // clientErrors object that holds error messages, if length [0] -> form submited
    const [clientErrors, setClientErrors] = useState({})
    // Message for sent news
    const [wasSuccessful, setWasSuccessful] = useState(false)
    // flag for image upload/verification of size
    const [imageUploaded, setImageUploaded] = useState(false)


    /* Cliente-side Form Validation before submiting the form(onSubmit)
    set error messages */
    function insertNews() {

        // each variable holds current value of the input accordingly and removing whitespace -> trim()
        const title = titleRef.current.value.trim();
        const description = descriptionRef.current.value.trim();
        const category = categoryRef.current.value;
        const image = imageRef.current.files[0]; // [0] cause it's allowed only one image, so it ghrabs the first, and .files -> PI for file inputs
        const image_rights = image_rights_Ref.current.checked; // to be checked in case of uploaded image
        const terms_conditions = terms_and_conditions_Ref.current.checked;

        // newErrors object starts at empty object
        const newErrors = {}

        // title validation
        if (title.length < 5 || title.length > 255) {
            newErrors.title = "O Título deve ter entre 5 e 255 caracteres."
        }

        // description validation
        if (description.length < 100 || description.length > 1050) {
            newErrors.description = "A Descrição deve ter entre 100 e 1050 caracteres."
        }


        // MAIS IMAGENS:  if (Object.keys(image).length > 9) {
        //     newErrors.image = "Selecione até 8 imagens."
        // }

        // if image was picked, needs to check image rights checkmark
        if (image) {

            if (!image_rights) {
                newErrors.image_rights = "É necessário autorizar a utilização da imagem."
            }
        } else { // no image was picked
            setImageUploaded(false)
        }

        // if terms and conditions isnt checked, there is error message
        if (!terms_conditions) {
            newErrors.terms_conditions = "É necessário aceitar a Política de Privacidade."
        } else {

        }

        // validacao checkbox terms and contidions --> erro, POR LÁ EM BAIXO NO FORM

        // check for the lenght of the object newErrors
        if (Object.keys(newErrors).length > 0) {
            setClientErrors(newErrors) // local state for displaying messages
            setWasSuccessful(false)
            return false // cancels the Inertia form submission
        } else {
            setClientErrors({})
            return true // sends form submission 
        }
    }

    function handleImageChange(event) {
        // event is the change fired by the input, target is the content
        const file = event.target.files[0]

        if (file) {

            const maxSize = 2 * 1024 * 1024; // igual ao max:2048 do StoreNewsRequest

            if (file.size > maxSize) {
                setImageUploaded(false) // rejects file
                setClientErrors(prev => ({ ...prev, image: "A imagem deve ter no máximo 2 MB." })) // prints the error message
            } else {
                setImageUploaded(true) // accept valid file
                setClientErrors(prev => ({ ...prev, image: null })) // overwites the image key error to null(no error shown anymore)
            }
        } else {
            setImageUploaded(false)
        }
    }



    // function that clears sucessMessage
    function clearSuccessMessage() {
        setWasSuccessful(false)
    }



    return (
        <div className="p-4">
            <div className="mx-auto">
                <h2 className="formTitle mt-3 mb-3">{formTitle}</h2>

                <Form className="formBody container shadow p-5"
                    method="POST"
                    action={route('news.store')}
                    noValidate // disables built-in html pop up messages
                    resetOnSuccess // resets visually all camps
                    onSuccess={() => { // if form gets accepted into the server, resets errors to empty array
                        setClientErrors({});
                        setWasSuccessful(true);
                    }}
                    onError={() => {
                        setWasSuccessful(false)
                    }}
                    onChange={clearSuccessMessage} // no Success Message as/if user changes any camp input
                    onBefore={insertNews} //before submiting calls the function for validation
                >

                    {/* children function needed for render/display */}
                    {({ processing, errors: serverErrors }) => {
                        // o <Form> do Inertia entrega aqui os erros devolvidos pelo
                        // servidor. Os do cliente ficam por cima por serem mais recentes.
                        const fieldErrors = { ...serverErrors, ...clientErrors }

                        return (
                        <>
                            {/* Honeypot: invisível para pessoas, visível no DOM para bots
                                que preenchem tudo o que encontram. O StoreNewsRequest
                                marca 'website' como prohibited, por isso qualquer valor
                                recusa a submissão. Escondido por CSS e não com
                                type="hidden": há bots que saltam campos ocultos. */}
                            <div aria-hidden="true" style={{ position: 'absolute', left: '-9999px' }}>
                                <label htmlFor="website">Website</label>
                                <input type="text" id="website" name="website" tabIndex={-1} autoComplete="off" />
                            </div>

                            <div className="">
                                <div>
                                    <label htmlFor="news-title">{titleLabel}</label>
                                </div>
                                <input ref={titleRef} name="title" id="news-title" type="text" minLength={5} maxLength={255}
                                    placeholder="Insira o título da notícia"
                                    onBlur={(event) => {
                                        const titleValue = event.target.value.trim()

                                        // the title is invalid if it's shorter than 5 or longer than 255.
                                        if (titleValue.length >= 5 && titleValue.length <= 255) {
                                            setClientErrors(prev => ({ ...prev, title: null }))
                                        }
                                    }} />

                                {/*ERROR MESSAGE - TITLE SIZE  */}
                                {fieldErrors.title && (
                                    <div className="mt-1 text-sm text-red-500">
                                        {fieldErrors.title}
                                    </div>
                                )}

                            </div>


                            <div className="mt-3">
                                <div>
                                    <label className="" htmlFor="news-description">{descriptionLabel}</label>
                                </div>
                                <textarea ref={descriptionRef} name="description" id="news-description"
                                    minLength={100} maxLength={1050} rows={6} cols={40}
                                    placeholder=" Descreva a notícia..."
                                    onBlur={(event) => {
                                        const descriptionValue = event.target.value.trim()
                                        // the description is invalid if it's shorter than 100 or longer than 1050.
                                        if (descriptionValue.length >= 100 && descriptionValue.length <= 1050) {
                                            setClientErrors(prev => ({ ...prev, description: null }))
                                        }
                                    }}
                                >
                                </textarea>   {/*verficiar se 1050 é muito ou pouco, admin pode editar anyways */}

                                {/* ERROR MESSAGE - DESCRIPTIONLabeldescriptionLabel SIZE  */}
                                {fieldErrors.description && (
                                    <div className="mt-1 text-sm text-red-500">
                                        {fieldErrors.description}
                                    </div>
                                )}
                            </div>


                            {/* * iterate this over the existing categories*/}
                            <div className="mt-3">
                                <div>
                                    <label htmlFor="news-category">{categoryLabel}</label>
                                </div>
                                <select ref={categoryRef} name="category_id" id="news-category">
                                    {/* Value for option "nenhuma" is empty string so it reaches the DB as null */}
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
                                    <label htmlFor="news-image">{imageLabel}</label>
                                </div>
                                <input ref={imageRef} name="image" id="news-image"
                                    type="file" accept="image/jpg, image/jpeg, image/png"
                                    onChange={handleImageChange}
                                />
                            </div>
                            {/* ERROR MESSAGE - IMAGE SIZE  */}
                            {fieldErrors.image && (
                                <div className="mt-1 text-sm text-red-500">
                                    {fieldErrors.image}
                                </div>
                            )}
                            {imageUploaded && <div><button type="button" onClick={() => {
                                imageRef.current.value = "" // clears the native file input
                                setImageUploaded(false)
                                setClientErrors(prev => ({ ...prev, image: null })) // no error message
                            }}>X REMOVER IMAGEM</button ></div>}


                            {/* CHECKBOX IMAGE RIGHTS*/}
                            <div className="mb-2 mt-4 form-check">
                                <input
                                    name='image_rights'
                                    ref={image_rights_Ref}
                                    type="checkbox"
                                    className="form-check-input"
                                    id="news-image-rights"
                                    disabled={!imageUploaded} // disabled if no image was uploaded
                                    onChange={(event) => {
                                        if (event.target.checked) {
                                            setClientErrors(prev => ({ ...prev, image_rights: null }))
                                        }
                                    }}
                                />
                                <label className="form-check-label" htmlFor="news-image-rights">
                                    Autorizo a utilização desta imagem para as finalidades relacionadas a este formulário.
                                </label>
                                {imageUploaded && fieldErrors.image_rights && <small className="mt-1 text-sm text-red-500" >{fieldErrors.image_rights}</small>}
                            </div>

                            {/* CHECKBOX TERMS AND CONDITIONS*/}
                            <div className="mb-2 form-check">
                                <input
                                    required
                                    name='terms_and_conditions'
                                    ref={terms_and_conditions_Ref}
                                    type="checkbox"
                                    className="form-check-input"
                                    id="terms-and-conditions"
                                    onChange={(event) => {
                                        if (event.target.checked) {
                                            setClientErrors(prev => ({ ...prev, terms_conditions: null }))
                                        }
                                    }}
                                />
                                <label className="form-check-label" htmlFor="terms-and-conditions">
                                    Aceito a Política de Privacidade, consentindo o tratamento dos meus dados pessoais nos termos do RGPD.
                                </label>
                                {fieldErrors.terms_conditions && <small className="mt-1 text-sm text-red-500" >{fieldErrors.terms_conditions}</small>}

                            </div>


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
                        )
                    }}

                </Form>
            </div>

        </div >

    )

} 
