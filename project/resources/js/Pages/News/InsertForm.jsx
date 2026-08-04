import NewsForm from "@/Components/NewsForm"
import { Form } from "@inertiajs/react"
import { useState, useEffect } from "react"


export default function InsertForm({ categories }) {


    /* Built-in function useForm that sets functionalities of a form to accept current data(empty strings/null), 
    setData for change (inputs), post request, processing and error messages, instead of writing 5 useStates and manual fetch */


    return (
        <div>
            <NewsForm
                formTitle='Inserir uma Notícia'
                titleLabel='Título'
                descriptionLabel='Descrição'
                categoryLabel='Categoria'
                imageLabel='Imagens'
                categories={categories}
            />
        </div>

    )
}