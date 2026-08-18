<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    private const TITLES = [
        'Curso de Excel Avançado',
        'Formação em Marketing Digital',
        'Gestão de Projetos com Metodologias Ágeis',
        'Introdução à Programação em Python',
        'Comunicação e Oratória em Contexto Profissional',
        'Liderança e Gestão de Equipas',
        'Contabilidade e Fiscalidade para PME',
        'Design Gráfico com Adobe Photoshop',
        'Atendimento ao Cliente e Vendas',
        'Empreendedorismo e Plano de Negócios',
        'Inglês Técnico para o Mercado de Trabalho',
        'Segurança e Saúde no Trabalho',
        'Gestão de Redes Sociais para Empresas',
        'Recursos Humanos e Gestão de Talento',
        'Introdução à Inteligência Artificial',
        'Excel para Análise de Dados',
        'Formação em Primeiros Socorros',
        'Gestão do Tempo e Produtividade',
    ];

    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(self::TITLES),
            'url' => fake()->url(),
            'imageUrl' => fake()->boolean(70) ? 'courses/'.fake()->uuid().'.jpg' : null,
            'location' => fake()->boolean(80) ? fake()->city() : null,
            'schedule' => fake()->boolean(80) ? fake()->dayOfWeek().' '.fake()->time('H:i') : null,
            'start_date' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'price' => number_format(fake()->randomFloat(2, 50, 2000), 2, '.', ''),
            'status' => 'received',
        ];
    }
}
