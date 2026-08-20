import NewsForm from "@/Components/NewsForm"
import PublicLayout from "@/Layouts/PublicLayout"
import { Head } from "@inertiajs/react"


// children {categories} is being loaded from the GET route
export default function InsertForm({ categories }) {


    /* returns the Component with customized labels and categories coming from DB::Category for the array to be presented in the select option*/
    return (

        <PublicLayout>
            <Head title="Inserir uma notícia" />

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
        </PublicLayout >
    )
}