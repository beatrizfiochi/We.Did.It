
import { useRef, useState } from "react";
import { Form, usePage } from "@inertiajs/react";
import SecondaryButton from "../SecondaryButton";

// component for a form that holds customized props, including the labels and input types.
// Any page that imports this component, defines its own submitFunction and validation rules.
export default function GeneralForm({ formTitle, formMethod, formAction, fields = [], submitFunction, clientErrors = {}, categoryList = [], maxImagens = 3 }) {
    // dá acesso ao getFormData() do <Form>, para a validação do cliente poder
    // ler os campos no onBefore sem depender de um evento de DOM
    const formRef = useRef(null)


    // os erros de validação do servidor chegam na prop partilhada `errors`.
    // Sem isto, uma submissão recusada pelo backend voltava sem explicação
    // nenhuma: o GeneralForm só mostrava os erros validados no cliente.
    const { errors: serverErrors = {}, flash } = usePage().props
    const errors = { ...serverErrors, ...clientErrors }

    const [imageUploaded, setImageUploaded] = useState(false)
    const [imagePreview, setImagePreview] = useState([])
    const [selectedFiles, setSelectedFiles] = useState([]) // preview da fila das 3 imagens escolhidas
    const [imageWarning, setImageWarning] = useState(null)
    const [fileInputKey, setFileInputKey] = useState(0)
    const [imageRights, setImageRights] = useState(false)
    const fileInputRef = useRef(null) // referencia do input dos ficheiros

    const [wasSuccessful, setWasSuccessful] = useState(false)
    const inputClass =
        'mt-1 block w-full rounded-md border-gray-300 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500'
    const checkboxClass =
        'mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-50'
    const errorClass = 'mt-1 block text-sm text-red-500'

    // checks if there is more than "0" files coming from the target(the input from the form that holds the image)
    function handleFileChange(event) {
        const newFiles = Array.from(event.target.files)

        // combina os ficheiros selecionados
        const combined = [...selectedFiles, ...newFiles] // sem slice para o utilizador receber erro se tentar carregar mais imagens que o valor maximo
        setImageWarning(
            combined.length > maxImagens
                ? `Só podes enviar ${maxImagens} imagens — as restantes foram ignoradas.`
                : null
        )
        const cappedImages = combined.slice(0, maxImagens) // corta só agora

        // sobrepoe o <input> tradicional para permitir escolher 3 ficheiros
        const dataTransfer = new DataTransfer()
        cappedImages.forEach((file) => dataTransfer.items.add(file)) // mapea entre os 3 escolhidos
        if (fileInputRef.current) {
            fileInputRef.current.files = dataTransfer.files
        }

        setSelectedFiles(cappedImages)
        setImageUploaded(cappedImages.length > 0)

        // se nao houver imagens a checkbox de direitos de imagem fica desligado
        if (cappedImages.length === 0) {
            setImageRights(false)
        }

        // cada imagem gera uma string URL diferente, e este URL é apagado sempre que a respetiva imagem seja alterada
        imagePreview.forEach((url) => URL.revokeObjectURL(url))
        setImagePreview(cappedImages.map((file) => URL.createObjectURL(file)))
    }

    function handleRemoveImage() {
        setImageUploaded(false);
        setImageRights(false);
        setSelectedFiles([]) // limpa o preview
        setFileInputKey(prev => prev + 1); // to "trick" react into checking the new state of the file, going from 0 to 1, and updating the state to a empty

        imagePreview.forEach((url) => URL.revokeObjectURL(url))
        setImagePreview([])
    }

    /**
     * Permite eliminar uma imagem do grupo escolhido
     * @param {*} index 
     */
    function handleRemoveOneImage(index) {
        // o preview atualizado vai filtrar entre os ficheiros selecionados verificando o key "i" se for diferente da imagem escolhida (index)
        /*a funcao "filter()" pede obrigatoriamente argumentos do value e index.
        O value pela configuração Eslint poderia ser declarado como um underscore "_" - uma variável não usada. */
        const updated = selectedFiles.filter((urlValue, i) => i !== index)

        // sobrepoe o <input> tradicional para permitir escolher 3 ficheiros
        const dataTransfer = new DataTransfer()
        updated.forEach((file) => dataTransfer.items.add(file)) // mapea entre os 3 escolhidos
        if (fileInputRef.current) {
            fileInputRef.current.files = dataTransfer.files
        }

        // exclui a imagem escolhida
        URL.revokeObjectURL(imagePreview[index])
        const updatedPreviews = imagePreview.filter((urlValue, i) => i !== index)

        setSelectedFiles(updated)
        setImagePreview(updatedPreviews)
        setImageWarning(null)
        setImageUploaded(updated.length > 0)
        if (updated.length === 0) {
            setImageRights(false)
        }
    }

    return (
        <div className="mt-5 mx-auto">
            <h3 className="mb-3 text-center">{formTitle}</h3>
            {/* text-gray-900 no formulário: o .public-layout define color #f8fafc
                para o texto assentar na fotografia de fundo, e isso é herdado cá
                dentro. Sem essa classe, tudo o que não traga cor própria fica
                branco sobre o cartão branco. */}
            <div className="mx-auto w-full max-w-2xl px-4">
                <p className="mb-3 text-center text-sm text-gray-500">
                    Os campos marcados com * são obrigatórios.
                </p>
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
                                        <label className="text-sm font-medium text-gray-700">
                                            {item.label}
                                            {/* se required= "true" adiciona apenas visualmente um "*", mas mantem escondido da leitor de ecrã */}
                                            {item.required && <span className="text-red-500" aria-hidden="true"> *</span>}
                                        </label>
                                        <div>
                                            {/* labelType[index] connects the labelType array to iterate on same positions as fields */}
                                            {/* if the type is file, onChange(if uploaded a file or removed) calls function */}
                                            {item.type === 'file' ? (
                                                <>
                                                    <input
                                                        className={inputClass}
                                                        type="file"
                                                        // key changes (0 → 1) so React treats this as a new input, not the old one
                                                        // it deletes the old DOM node (with the file inside) and mounts a fresh, empty one
                                                        key={fileInputKey}
                                                        ref={fileInputRef}
                                                        accept="image/jpg,image/jpeg,image/png"
                                                        multiple
                                                        // o [] é o que faz o campo chegar ao servidor como array, que é o
                                                        // que as regras 'images' e 'images.*' esperam (SCRUM-140). Sem ele
                                                        // o ficheiro chegava com o nome antigo e era descartado em silêncio
                                                        name={`${item.name}[]`}
                                                        onChange={handleFileChange}
                                                    />

                                                    {/* Fila das images preview */}
                                                    {imagePreview.length > 0 && (
                                                        <>
                                                            <div className="mt-2 grid grid-cols-3 gap-2">
                                                                {imagePreview.map((url, i) => // cada imagem gera uma string URL diferente
                                                                    <div key={url} className="relative">
                                                                        <img
                                                                            src={url}
                                                                            alt={`Pré-visualização ${i + 1}`}
                                                                            className="h-24 w-full rounded-md border border-gray-200 object-cover"
                                                                        />

                                                                        <button
                                                                            type="button"
                                                                            onClick={() => handleRemoveOneImage(i)}
                                                                            className="absolute top-1 right-1 rounded-full bg-black/60 text-white w-5 h-5 text-xs leading-5">
                                                                            x
                                                                        </button>
                                                                    </div>
                                                                )}
                                                            </div>
                                                            {imageWarning && (
                                                                <small className={errorClass}>{imageWarning}</small>
                                                            )}
                                                        </>
                                                    )}
                                                </>


                                            ) : item.type === 'textarea' ? (

                                                <textarea className={inputClass} rows={4} name={item.name} aria-required={item.required} />

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
                                                <input className={inputClass} type={item.type} name={item.name} aria-required={item.required} />
                                            )}

                                        </div>

                                        {/* Os erros de ficheiro chegam por posição — images.0, images.1 —
                                            além do erro do conjunto, em images. Mostrar só errors[item.name]
                                            deixava o utilizador a escolher um PDF e a não ver aviso nenhum. */}
                                        {
                                            Object.entries(errors)
                                                .filter(([key]) => key === item.name || key.startsWith(`${item.name}.`))
                                                .map(([key, message]) => (
                                                    <small key={key} className={errorClass}>{message}</small>
                                                ))
                                        }
                                        {item.type === 'file' && imageWarning && (
                                            <SecondaryButton onClick={() => setImageWarning(null)} aria-label="Fechar aviso" className="mb-3">Ok</SecondaryButton>
                                        )}

                                        {item.type === 'file' && imageUploaded && <div><button type="button" className="mt-1 text-sm font-semibold text-red-600 hover:text-red-800" onClick={handleRemoveImage}>Remover imagens</button></div>}

                                    </div>
                                ))}



                                {/* image rights - only enabled once a file is picked */}
                                {imageUploaded && (
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
                                        />

                                        <div>
                                            <label className="text-sm text-gray-700" htmlFor="image_rights">
                                                Autorizo a utilização {
                                                    selectedFiles.length === 1
                                                        ? 'desta imagem'
                                                        : 'destas imagens'
                                                } para as finalidades relacionadas a este formulário.
                                            </label>
                                            {imageUploaded && errors.image_rights && (
                                                <small className={errorClass}>{errors.image_rights}</small>
                                            )}
                                        </div>

                                    </div>)}

                                {/* terms and conditions */}
                                <div className="mb-3 flex items-start gap-2">
                                    <input type="checkbox" className={checkboxClass} id="terms-conditions" name="terms_conditions" aria-required="true" />
                                    <div>
                                        {/* este componente serve os formulários de notícias e de
                                            testemunhos — o link à Política de Privacidade cobre os dois
                                            pedidos do cliente de uma vez (SCRUM-138) */}
                                        <label className="text-sm text-gray-700" htmlFor="terms-conditions">
                                            Aceito a{' '}
                                            <a
                                                href="https://www.cesaedigital.pt/fldrSite/pages/privacyPolicy.aspx"
                                                target="_blank"
                                                rel="noreferrer"
                                                className="font-semibold text-indigo-600 hover:text-indigo-800"
                                            >
                                                Política de Privacidade
                                            </a>.
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
