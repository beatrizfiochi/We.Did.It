
import { useRef, useState } from "react";
import { Form, usePage } from "@inertiajs/react";

// component for a form that holds customized props, including the labels and input types.
// Any page that imports this component, defines its own submitFunction and validation rules.
export default function GeneralForm({ formTitle, formMethod, formAction, fields = [], submitFunction, clientErrors = {}, categoryList = [] }) {

    // dá acesso ao getFormData() do <Form>, para a validação do cliente poder
    // ler os campos no onBefore sem depender de um evento de DOM
    const formRef = useRef(null)


    // os erros de validação do servidor chegam na prop partilhada `errors`.
    // Sem isto, uma submissão recusada pelo backend voltava sem explicação
    // nenhuma: o GeneralForm só mostrava os erros validados no cliente.
    const { errors: serverErrors = {}, flash } = usePage().props
    const errors = { ...serverErrors, ...clientErrors }

    const [imageUploaded, setImageUploaded] = useState(false)
    const [fileInputKey, setFileInputKey] = useState(0)
    const [imageRights, setImageRights] = useState(false)
    const [wasSuccessful, setWasSuccessful] = useState(false)
    const inputClass =
        'mt-1 block w-full rounded-md border-gray-300 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500'
    const checkboxClass =
        'mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-50'
    const errorClass = 'mt-1 block text-sm text-red-500'

    // checks if there is more than "0" files coming from the target(the input from the form that holds the image)
    function handleFileChange(event) {
        const hasFile = event.target.files.length > 0
        setImageUploaded(hasFile)
        if (!hasFile) {
            setImageRights(false)
        }
    }

    function handleRemoveImage() {
        setImageUploaded(false);
        setImageRights(false);
        setFileInputKey(prev => prev + 1); // to "trick" react into checking the new state of the file, going from 0 to 1, and updating the state to a empty
    }



    return (
        <div className="mt-5 mx-auto">
            <h3 className="mb-3 text-center">{formTitle}</h3>
            {/* text-gray-900 no formulário: o .public-layout define color #f8fafc
                para o texto assentar na fotografia de fundo, e isso é herdado cá
                dentro. Sem essa classe, tudo o que não traga cor própria fica
                branco sobre o cartão branco. */}
            <div className="mx-auto w-full max-w-2xl px-4">
                <Form
                    ref={formRef}
                    action={formAction}
                    method={formMethod}
                    encType="multipart/form-data"
                    noValidate
                    resetOnSuccess
                    onBefore={() => submitFunction(formRef.current.getFormData())}
                    onSuccess={() => {
                        setWasSuccessful(true)
                        handleRemoveImage()
                    }}
                    onError={() => setWasSuccessful(false)}
                    onChange={() => setWasSuccessful(false)}
                    className="rounded-lg bg-white p-4 text-gray-900 shadow sm:p-6">
                    {({ processing }) => (
                            <>
                            {/* Honeypot: invisível para pessoas, presente no DOM para bots
                                que preenchem tudo o que encontram. Só tem efeito se o
                                FormRequest do destino marcar 'website' como prohibited —
                                sem isso é um campo vazio inofensivo. Escondido por CSS e
                                não com type="hidden": há bots que saltam campos ocultos. */}
                            <div aria-hidden="true" style={{ position: 'absolute', left: '-9999px' }}>
                                <label htmlFor="website">Website</label>
                                <input type="text" id="website" name="website" tabIndex={-1} autoComplete="off" />
                            </div>

                            <div>

                                {fields.map((item, index) => (
                                    <div key={item.name} className="mb-3">
                                        <label className="text-sm font-medium text-gray-700">{item.label}</label>
                                        <div>
                                            {/* labelType[index] connects the labelType array to iterate on same positions as fields */}
                                            {/* if the type is file, onChange(if uploaded a file or removed) calls function */}
                                            {item.type === 'file' ? (
                                                <input
                                                    className={inputClass}
                                                    type="file"
                                                    // key changes (0 → 1) so React treats this as a new input, not the old one 
                                                    // it deletes the old DOM node (with the file inside) and mounts a fresh, empty one
                                                    key={fileInputKey}
                                                    accept="image/jpg,image/jpeg,image/png"
                                                    name={item.name}
                                                    onChange={handleFileChange}
                                                />
                                                // if type is a textarea sets sizing rules 

                                            ) : item.type === 'textarea' ? (

                                                <textarea className={inputClass} rows={4} name={item.name} />
                                            ) : item.type === 'select' ? (
                                                <>
                                                    <select className={inputClass} name={item.name} id="news-category">
                                                        {/* Value for option "nenhuma" is empty string so it reaches the DB as null */}
                                                        <option value="" default>Nenhuma</option>
                                                        {categoryList.map((category) =>
                                                            <option key={category.id} value={category.id}>{category.name}</option>
                                                        )}
                                                    </select>
                                                    {/* Error Message - no Categories available */}
                                                    {categoryList.length === 0 && <p className="mt-1 text-sm text-red-500">Não existem categorias disponíveis.</p>}
                                                </>
                                            ) : (

                                                // input type holds type assigned in the labelType array, same position as current item from userLabel
                                                <input className={inputClass} type={item.type} name={item.name} />
                                            )}

                                            {item.type === 'file' && imageUploaded && <div><button type="button" className="mt-1 text-sm font-semibold text-red-600 hover:text-red-800" onClick={handleRemoveImage}>Remover imagem</button></div>}

                                        </div>
                                        {/* if there is an error associated to item, it shows under input */}
                                        {errors[item.name] && <small className={errorClass}>{errors[item.name]}</small>}
                                    </div>
                                ))}



                                {/* image rights - only enabled once a file is picked */}
                                <div className="mb-3 flex items-start gap-2">
                                    <input
                                        type="checkbox"
                                        className={checkboxClass}
                                        id="image_rights"
                                        name="image_rights"
                                        // if checked it means imageRights is true
                                        checked={imageRights}
                                        // since the image is now controlled by checked, if user tickes it it becomes false and vice-versa
                                        onChange={(click) => setImageRights(click.target.checked)}
                                        disabled={!imageUploaded}
                                    />
                                    <div>
                                        <label className="text-sm text-gray-700" htmlFor="image_rights">
                                            Autorizo a utilização desta imagem para as finalidades relacionadas a este formulário.
                                        </label>
                                        {imageUploaded && errors.image_rights && (
                                            <small className={errorClass}>{errors.image_rights}</small>
                                        )}
                                    </div>
                                </div>

                                {/* terms and conditions */}
                                <div className="mb-3 flex items-start gap-2">
                                    <input type="checkbox" className={checkboxClass} id="terms-conditions" name="terms_conditions" />
                                    <div>
                                        <label className="text-sm text-gray-700" htmlFor="terms-conditions">
                                            Aceito a Política de Privacidade.
                                        </label>
                                        {errors.terms_conditions && (
                                            <small className={errorClass}>{errors.terms_conditions}</small>
                                        )}
                                    </div>
                                </div>
                            </div>
                            <button
                                type="submit"
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                                disabled={processing}
                            >
                                Enviar
                            </button>
                            <div>
                                {wasSuccessful && flash?.success && (
                                    <small className="mt-3 block text-sm font-medium text-green-700">
                                        {flash.success}
                                    </small>
                                )}
                            </div>
                            </>
                            )}
                </Form>
            </div >
        </div >

    );

}
