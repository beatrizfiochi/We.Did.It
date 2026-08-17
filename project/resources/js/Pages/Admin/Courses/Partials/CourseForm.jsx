import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';

export default function CourseForm({ data, setData, errors }) {
    return (
        <div className="space-y-6">
            <div>
                <InputLabel htmlFor="title" value="Título" />

                <TextInput
                    id="title"
                    name="title"
                    value={data.title}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('title', e.target.value)}
                    required
                />

                <InputError message={errors.title} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="url" value="URL" />

                <TextInput
                    id="url"
                    type="url"
                    name="url"
                    value={data.url}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('url', e.target.value)}
                    required
                />

                <InputError message={errors.url} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="imageUrl" value="Imagem" />

                <TextInput
                    id="imageUrl"
                    name="imageUrl"
                    value={data.imageUrl}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('imageUrl', e.target.value)}
                    placeholder="URL ou caminho da imagem"
                />

                <InputError message={errors.imageUrl} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="location" value="Local" />

                <TextInput
                    id="location"
                    name="location"
                    value={data.location}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('location', e.target.value)}
                />

                <InputError message={errors.location} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="schedule" value="Horário" />

                <TextInput
                    id="schedule"
                    name="schedule"
                    value={data.schedule}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('schedule', e.target.value)}
                    placeholder="Ex.: Segunda e quarta, 19h-22h"
                />

                <InputError message={errors.schedule} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="start_date" value="Data de início" />

                <TextInput
                    id="start_date"
                    type="date"
                    name="start_date"
                    value={data.start_date}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('start_date', e.target.value)}
                    required
                />

                <InputError message={errors.start_date} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="price" value="Preço" />

                <TextInput
                    id="price"
                    name="price"
                    value={data.price}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('price', e.target.value)}
                    placeholder="Ex.: 250.00"
                    required
                />

                <InputError message={errors.price} className="mt-2" />
            </div>

            <div>
                <InputLabel htmlFor="status" value="Estado" />

                <SelectInput
                    id="status"
                    name="status"
                    value={data.status}
                    className="mt-1 block w-full"
                    onChange={(e) => setData('status', e.target.value)}
                    required
                >
                    <option value="received">Recebido</option>
                    <option value="accepted">Aceite</option>
                    <option value="refused">Recusado</option>
                </SelectInput>

                <InputError message={errors.status} className="mt-2" />
            </div>
        </div>
    );
}
