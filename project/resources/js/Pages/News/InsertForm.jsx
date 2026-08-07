import NewsForm from "@/Components/NewsForm"


// children {categories} is being loaded from the GET route
export default function InsertForm({ categories }) {


    /* returns the Component with customized labels and categories coming from DB::Category for the array to be presented in the select option*/
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