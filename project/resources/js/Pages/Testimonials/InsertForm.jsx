import FileInput from '@/Components/FileInput';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SelectInput from '@/Components/SelectInput';
import TextArea from '@/Components/TextArea';
import TextInput from '@/Components/TextInput';
import PublicLayout from '@/Layouts/PublicLayout';
import { Head, useForm } from '@inertiajs/react';

/*
 * ⚠️ ECRÃ PROVISÓRIO — substituir pelo formulário definitivo.
 *
 * O SCRUM-90 era só backend (rota, controller e validação). Este ficheiro
 * existe porque sem ele a rota /testemunhos/novo devolve 500: o
 * app.blade.php procura resources/js/Pages/<componente>.jsx no manifest do
 * Vite, e rebenta se não encontrar. Não é opcional, é o que faz a rota viver.
 *
 * Já funciona: os 6 campos, o upload da imagem, os erros de validação
 *              devolvidos pelo servidor e a mensagem de sucesso.
 * Falta:       o desenho a sério, e as checkboxes de direitos de imagem e
 *              RGPD que o NewsForm.jsx tem (ver o ponto 6 do plano — hoje
 *              nenhuma das duas é guardada, nem aqui nem nas notícias).
 *
 * ⛔ NÃO MUDES os atributos `name` dos inputs sem mudar também as rules do
 *    App\Http\Requests\StoreTestimonialRequest. São o contrato com o backend:
 *    name, email, title, description, category_id, image.
 *    O aspeto muda à vontade; os names partem a submissão sem dar erro claro.
 */
export default function InsertForm({ categories = [] }) {
    const { data, setData, post, processing, errors, reset, wasSuccessful } =
        useForm({
            name: '',
            email: '',
            title: '',
            description: '',
            category_id: '',
            image: null,
        });

    const submit = (e) => {
        e.preventDefault();

        post(route('testimonials.store'), {
            forceFormData: true, // necessário por causa do upload da imagem
            onSuccess: () => reset(),
        });
    };

    return (
        <PublicLayout>
            <Head title="Inserir um testemunho" />

            <section className="mx-auto w-full max-w-2xl px-6 py-12">
                <h1 className="mb-6 text-2xl font-bold">Inserir um Testemunho</h1>

                {wasSuccessful && (
                    <div className="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                        O teu testemunho foi enviado para aprovação.
                    </div>
                )}

                <form onSubmit={submit} className="space-y-4" noValidate>
                    <div>
                        <InputLabel htmlFor="name" value="Nome" />
                        <TextInput
                            id="name"
                            name="name"
                            value={data.name}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        <InputError message={errors.name} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        <InputError message={errors.email} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="title" value="Título" />
                        <TextInput
                            id="title"
                            name="title"
                            value={data.title}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('title', e.target.value)}
                        />
                        <InputError message={errors.title} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="description" value="Descrição" />
                        <TextArea
                            id="description"
                            name="description"
                            rows={6}
                            value={data.description}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('description', e.target.value)}
                        />
                        <InputError message={errors.description} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="category_id" value="Categoria" />
                        <SelectInput
                            id="category_id"
                            name="category_id"
                            value={data.category_id}
                            className="mt-1 block w-full"
                            onChange={(e) => setData('category_id', e.target.value)}
                        >
                            {/* valor vazio para chegar à base de dados como null */}
                            <option value="">Nenhuma</option>
                            {categories.map((category) => (
                                <option key={category.id} value={category.id}>
                                    {category.name}
                                </option>
                            ))}
                        </SelectInput>
                        <InputError message={errors.category_id} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="image" value="Imagem" />
                        <FileInput
                            id="image"
                            name="image"
                            accept="image/jpeg,image/png"
                            className="mt-1"
                            onChange={(e) => setData('image', e.target.files[0])}
                        />
                        <InputError message={errors.image} className="mt-2" />
                    </div>

                    <PrimaryButton disabled={processing}>Submeter</PrimaryButton>
                </form>
            </section>
        </PublicLayout>
    );
}
