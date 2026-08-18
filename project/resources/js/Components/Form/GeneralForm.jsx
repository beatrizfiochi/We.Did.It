
import { useState } from "react";

// component for a form that holds customized props, including the labels and input types.
// Any page that imports this component, defines its own submitFunction and validation rules.
export default function GeneralForm({ formTitle, formMethod, formAction, fields = [], submitFunction, clientErrors = {}, categoryList = [], successMessage, clearSuccessMessage }) {


    const [imageUploaded, setImageUploaded] = useState(false)
    const [fileInputKey, setFileInputKey] = useState(0)
    const [imageRights, setImageRights] = useState(false)
    const [wasSuccessful, setWasSuccessful] = useState(false)

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
            <h3 className="text-center mb-3">{formTitle}</h3>
            <div className="container">
                <div className="row justify-content-center">
                    <div className="col-6">
                        <form
                            action={formAction}
                            method={formMethod}
                            encType="multipart/form-data"
                            onSubmit={submitFunction}
                            onChange={clearSuccessMessage}
                            className="container shadow p-3">
                            <div>
                                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').content} />

                                {fields.map((item, index) => (
                                    <div key={item.name} className="mb-3">
                                        <label>{item.label}</label>
                                        <div>
                                            {/* labelType[index] connects the labelType array to iterate on same positions as fields */}
                                            {/* if the type is file, onChange(if uploaded a file or removed) calls function */}
                                            {item.type === 'file' ? (
                                                <input
                                                    className="form-control"
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

                                                <textarea className="form-control" rows={4} name={item.name} />
                                            ) : item.type === 'select' ? (
                                                <>
                                                    <select name={item.name} id="news-category">
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
                                                <input className="form-control" type={item.type} name={item.name} />
                                            )}

                                            {item.type === 'file' && imageUploaded && <div><button type="button" onClick={handleRemoveImage}>X REMOVER IMAGEM</button></div>}

                                        </div>
                                        {/* if there is an error associated to item, it shows under input */}
                                        {clientErrors[item.name] && <small className="mt-1 text-sm text-red-500">{clientErrors[item.name]}</small>}
                                    </div>
                                ))}



                                {/* image rights - only enabled once a file is picked */}
                                <div className="mb-3 form-check">
                                    <input
                                        type="checkbox"
                                        className="form-check-input"
                                        id="image_rights"
                                        name="image_rights"
                                        // if checked it means imageRights is true
                                        checked={imageRights}
                                        // since the image is now controlled by checked, if user tickes it it becomes false and vice-versa
                                        onChange={(click) => setImageRights(click.target.checked)}
                                        disabled={!imageUploaded}
                                    />
                                    <label className="form-check-label" htmlFor="image_rights">
                                        Autorizo a utilização desta imagem para as finalidades relacionadas a este formulário.
                                    </label>
                                    {imageUploaded && clientErrors.image_rights && (
                                        <small className="d-block text-danger">{clientErrors.image_rights}</small>
                                    )}
                                </div>

                                {/* terms and conditions */}
                                <div className="mb-3 form-check">
                                    <input type="checkbox" className="form-check-input" id="terms-conditions" name="terms_conditions" />
                                    <label className="form-check-label" htmlFor="terms-conditions">
                                        Aceito a Política de Privacidade.
                                    </label>
                                    {clientErrors.terms_conditions && (
                                        <small className="d-block text-danger">{clientErrors.terms_conditions}</small>
                                    )}
                                </div>
                            </div>
                            <button type="submit" className="btn btn-primary">Enviar</button>
                            <div>
                                {successMessage && (
                                    <small id="testimonial-form-root" className="mt-3 text-sm bg-success">
                                        {successMessage}
                                    </small>
                                )}
                            </div>
                        </form>

                    </div>
                </div>
            </div >
        </div >

    );

}