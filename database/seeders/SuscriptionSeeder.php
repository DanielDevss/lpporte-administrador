<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Suscription;


class SuscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suscriptions = [
            'Free' => [
                'name' => 'Gratis',
                'amount' => 0,
                'free' => true,
                'attributes' => [
                    '❌ Descuento en playera cuello redondo',
                    '❌ Descuento en playera polo calidad premium',
                    '❌ Descuento en pantalones originales',
                    '❌ Descuento en sudaderas',
                    '❌ Descuento en chamarras',
                ],
                'benefits' => [
                    '❌ Aprenderás a vender a nivel nacional',
                    '❌ Descuentos exclusivos en envios',
                    '❌ Nosotros realizamos tus envios nacionales',
                    '❌ Asesoramiento en redes sociales',
                    '❌ Al afiliarte en redes sociales podrás ofrecer membresías y generar el 100% del ingreso'
                ],
            ],
            
            'Basic' => [
                'name' => 'Básico',
                'amount' => 149900,
                'free' => false,
                'attributes' => [
                    '✅ Playera cuello redondo desde MX$99.00',
                    '✅ Playera polo calidad premium desde MX$249.00',
                    '❌ Descuento en pantalones originales',
                    '❌ Descuento en sudaderas',
                    '❌ Descuento en chamarras',
                ],
                'benefits' => [
                    '❌ Aprenderás a vender a nivel nacional',
                    '❌ Descuentos exclusivos en envios',
                    '❌ Nosotros realizamos tus envios nacionales',
                    '❌ Asesoramiento en redes sociales',
                    '❌ Al afiliarte en redes sociales podrás ofrecer membresías y generar el 100% del ingreso'
                ],
            ],
            'Premium' => [
                'name' => 'Premium',
                'amount' => 349900,
                'free' => false,
                'attributes' => [
                    '✅ Playera cuello redondo desde MX$79.00',
                    '✅ Playera polo calidad premium desde MX$199.00',
                    '✅ Pantalones originales desde MX$259.00',
                    '✅ Sudaderas desde MX$259.00',
                    '✅ Chamarras desde MX$269.00',
                ],
                'benefits' => [
                    '✅ Aprenderás a vender a nivel nacional',
                    '✅ Descuentos exclusivos en envios',
                    '✅ Nosotros realizamos tus envios nacionales',
                    '✅ Asesoramiento en redes sociales',
                    '✅ Al afiliarte en redes sociales podrás ofrecer membresías y generar el 100% del ingreso',
                ]
            ]
        ];
        
        foreach ($suscriptions as $suscription) {
            Suscription::updateOrCreate(
                ['name' => $suscription['name']],
                [
                    'amount' => $suscription['amount'],
                    'free' => $suscription['free'],
                    'attributes' => $suscription['attributes'],
                    'benefits' => $suscription['benefits'],
                ]
            );
        }
    }

}
