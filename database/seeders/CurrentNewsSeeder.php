<?php

namespace Database\Seeders;

use App\Models\Debate;
use App\Models\Message;
use App\Models\Signature;
use App\Models\User;
use App\Models\VoteTopic;
use App\Support\IdentityHasher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CurrentNewsSeeder extends Seeder
{
    /** @var array<string, User> */
    private array $voices = [];

    public function run(): void
    {
        $this->wipeCatalog();
        $this->voices();
        $this->seedDebates();
        $this->seedVoteTopics();
    }

    private function wipeCatalog(): void
    {
        Schema::disableForeignKeyConstraints();
        Signature::query()->truncate();
        VoteTopic::query()->truncate();
        Message::query()->truncate();
        Debate::query()->truncate();
        Schema::enableForeignKeyConstraints();
    }

    private function voices(): void
    {
        $people = [
            'redaccion' => 'Redacción',
            'vecino_lpz' => 'Vecino LPZ',
            'yungas_verde' => 'Yungas Verde',
            'crucena_prod' => 'Cruceña Prod',
            'paceno_sur' => 'Paceño Sur',
            'chofer_scz' => 'Chofer SCZ',
            'docente_oruro' => 'Docente Oruro',
            'sindical_lpz' => 'Sindical LPZ',
            'joven_scr' => 'Joven SCR',
            'mineria_potosi' => 'Minería Potosí',
            'madre_elalto' => 'Madre El Alto',
            'agro_beni' => 'Agro Beni',
            'uni_cbba' => 'Uni Cbba',
            'comerciante_tz' => 'Comerciante Tarija',
        ];

        foreach ($people as $nick => $name) {
            $this->voices[$nick] = $this->voice($nick, $name);
        }
    }

    private function voice(string $nick, string $name): User
    {
        $existing = User::query()->where('nick', $nick)->first();
        if ($existing) {
            return $existing;
        }

        $email = $nick.'@viejocracia.test';

        return User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'nick' => $nick,
                'email_hash' => app(IdentityHasher::class)->hashEmail($email),
                'password' => 'password',
            ]
        );
    }

    private function seedDebates(): void
    {
        $items = $this->debates();
        $total = count($items);

        foreach ($items as $index => $item) {
            $debate = Debate::factory()->for($this->voices['redaccion'], 'author')->create([
                'title' => $item['title'],
                'category' => $item['category'],
                'description' => $item['description'],
                'image_path' => $this->storeCover($item['cover']),
                'image_credit' => $item['credit'],
                'sources' => $item['sources'],
                'created_at' => now()->subDays($total - $index)->subHours(3),
                'updated_at' => now()->subHours($total - $index),
            ]);

            $this->seedThread($debate, $item['thread']);
        }
    }

    /**
     * @param  list<array{nick: string, text: string, replies?: list<array{nick: string, text: string, replies?: list<array{nick: string, text: string}>}>}>  $thread
     */
    private function seedThread(Debate $debate, array $thread, ?Message $parent = null, int $startMinutes = 180): void
    {
        foreach ($thread as $offset => $post) {
            $message = Message::query()->create([
                'debate_id' => $debate->id,
                'user_id' => $this->voices[$post['nick']]->id,
                'content' => $post['text'],
                'parent_id' => $parent?->id,
                'created_at' => now()->subMinutes($startMinutes - ($offset * 12)),
                'updated_at' => now()->subMinutes($startMinutes - ($offset * 12)),
            ]);

            if (! empty($post['replies'])) {
                $this->seedThread($debate, $post['replies'], $message, $startMinutes - ($offset * 12) - 6);
            }
        }
    }

    private function storeCover(string $filename): ?string
    {
        $source = database_path('seeders/covers/'.$filename);
        if (! is_file($source)) {
            return null;
        }

        $path = 'debates/images/'.$filename;
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }

    /**
     * @return list<array{title: string, category: string, description: string, cover: string, credit: string, sources: list<array{name: string, url: string}>, thread: list<array<string, mixed>>}>
     */
    private function debates(): array
    {
        return [
            [
                'title' => 'Sobresueldos de 5.000 dólares: ¿se compran votos en la Asamblea?',
                'category' => 'politica',
                'cover' => 'alp-legislativo.jpg',
                'credit' => 'Palacio Legislativo, Plaza Murillo, La Paz. Wikimedia Commons / Yolipy (CC BY-SA 4.0).',
                'sources' => [
                    ['name' => 'Visión 360 — denuncia de sobresueldos en la ALP', 'url' => 'https://www.vision360.bo/noticias/2026/08/20/63634-denuncia-de-sobresueldos-causa-un-remezon-en-la-alp-y-legisladores-demandan-una-investigacion-a-fondo'],
                    ['name' => 'eju.tv — Libre pide levantar el secreto bancario', 'url' => 'https://eju.tv/2026/08/sobresueldos-libre-pide-a-legisladores-y-autoridades-del-gobierno-renunciar-al-secreto-bancario-y-pdc-investigar-pagos/'],
                    ['name' => 'Éxito Noticias — lista de Beller y pedido de pruebas', 'url' => 'https://exitonoticias.com.bo/tras-ser-mencionados-en-la-lista-de-beller-diputados-exigen-pruebas-y-solicitan-levantar-el-secreto-bancario/400908/'],
                ],
                'description' => 'En agosto de 2026, Nadia Beller denunció presuntos pagos mensuales de 5.000 dólares a legisladores de Libre y Unidad a cambio de aprobar normas y frenar censuras. Libre pidió levantar el secreto bancario; el PDC habló de comisión investigadora. Hasta ahora no hay sentencia: hay una lista, un remezón en la ALP y una pregunta cruda. ¿La Asamblea se fiscaliza o se alquila?',
                'thread' => [
                    [
                        'nick' => 'redaccion',
                        'text' => 'Abrimos el hilo con lo que está en la calle: denuncia de sobresueldos, lista circulando y asambleístas que piden pruebas o secreto bancario. Argumentos, no linchamiento. Los comentarios de este debate son de participantes de la plataforma.',
                    ],
                    [
                        'nick' => 'joven_scr',
                        'text' => 'Si un diputado necesita 5.000 dólares extra para “acompañar” al Ejecutivo, no es legislador: es operador. Da igual el color. La Viejocracia no es edad, es atrincherarse con la chequera ajena.',
                        'replies' => [
                            [
                                'nick' => 'paceno_sur',
                                'text' => 'Concuerdo en el fondo, pero una lista sin documentos no es prueba. Si mañana me ponen a mí, ¿también me linchan en redes y después “ah, era un error”?',
                            ],
                            [
                                'nick' => 'joven_scr',
                                'text' => 'Por eso hay que levantar secreto bancario de todos, no de los que caen mal. Si están limpios, 48 horas y se acaba el show.',
                            ],
                        ],
                    ],
                    [
                        'nick' => 'crucena_prod',
                        'text' => 'En Santa Cruz ya nadie se sorprende. El que cree que las leyes salen solo por ideología no ha visto cómo se arman las mayorías. Lo grave es que el diésel falta y ellos discutirían la cifra.',
                    ],
                    [
                        'nick' => 'sindical_lpz',
                        'text' => 'Ojo: esto también se usa para golpear a Quiroga y a Libre. Las listas van y vienen cada crisis. Quiero investigación, no un ajuste de cuentas disfrazado de moral.',
                        'replies' => [
                            [
                                'nick' => 'uni_cbba',
                                'text' => 'Las dos cosas pueden ser ciertas: que haya operador sucio y que la denuncia sirva de granada. Por eso la Fiscalía tiene que trabajar con papeles, no con capturas de WhatsApp.',
                            ],
                        ],
                    ],
                    [
                        'nick' => 'madre_elalto',
                        'text' => 'Yo hago fila por un pan y un litro. Ellos discuten si 5.000 dólares es “colaboración política”. Que publiquen cuentas de ellos y de la familia. Sin teatro.',
                    ],
                    [
                        'nick' => 'docente_oruro',
                        'text' => 'La comisión de la Asamblea investigándose a sí misma es un chiste. Tiene que ser externa, con plazos y con informe público. Si no, en dos semanas esto se pudre y nadie dimite.',
                    ],
                ],
            ],
            [
                'title' => 'Diésel a 18 bolivianos para industrias: ¿sinceramiento o otro tarifazo?',
                'category' => 'economia',
                'cover' => 'ypfb-lapaz.jpg',
                'credit' => 'Edificio YPFB, La Paz. Wikimedia Commons / EEJCC (CC BY-SA 4.0).',
                'sources' => [
                    ['name' => 'EFE — Bolivia quita la subvención al diésel para grandes consumidores', 'url' => 'https://efe.com/economia/2026-08-17/bolivia-subvencion-precio-diesel-consumidores-combustible/'],
                    ['name' => 'AP News — crisis de combustibles y decreto 5676', 'url' => 'https://apnews.com/article/bolivia-rodrigo-paz-crisis-energetica-subsidios-crisis-economica-7c3d07fa22adc934f381827716c5e6b6'],
                ],
                'description' => 'El Decreto Supremo 5676 quitó la subvención al diésel para grandes consumidores e industrias. El referencial subió cerca de 18 Bs el litro; transporte y particulares se mantienen en torno a 9,80. El Gobierno habla de filas, de “clientes directos” y de años de subvención financiada con reservas. Los productores hablan de costos, alimentos y paralización. ¿Se acaba el privilegio industrial o se transfiere la crisis al precio de la comida?',
                'thread' => [
                    [
                        'nick' => 'redaccion',
                        'text' => 'El decreto ya está en vigor: industrias y grandes consumidores pagan diésel sin subvención. ¿Es el único camino para que haya combustible en surtidor, o es el comienzo de otro tarifazo en cadena?',
                    ],
                    [
                        'nick' => 'agro_beni',
                        'text' => 'En el Beni el agro no es “gran consumidor de lujo”. Sin diésel no hay cosecha ni flete. Si me lo ponen a 18, el arroz y la carne no van a esperar el discurso de sinceramiento: van a subir la semana que viene.',
                        'replies' => [
                            [
                                'nick' => 'crucena_prod',
                                'text' => 'Exacto. El soya, el transporte de carga y la industria alimentaria somos el mismo eslabón. Quieren “salvar el surtidor” asfixiando al que produce.',
                            ],
                            [
                                'nick' => 'vecino_lpz',
                                'text' => 'Y si no lo hacen, el surtidor sigue seco y el productor igual para. El subsidio infinito ya quebró las reservas. El problema es el cómo y a quién se le cobra primero.',
                            ],
                        ],
                    ],
                    [
                        'nick' => 'chofer_scz',
                        'text' => 'A mí me dejaron el precio “social” y igual hago cola desde las 4 de la mañana. Entonces ¿la subvención llega al que maneja, o se la quedan los que tienen cupo?',
                    ],
                    [
                        'nick' => 'mineria_potosi',
                        'text' => 'En minería el diésel es costo directo. Sin regla clara de quién es “grande consumidor” esto se presta a amigo y enemigo. Publiquen el padrón de cupos, no un PowerPoint.',
                    ],
                    [
                        'nick' => 'yungas_verde',
                        'text' => 'Durante años se subvencionó con oro y reservas para que el precio mienta. Eso también es Viejocracia económica: patear la cuenta a la generación que viene. Duele, pero el precio falso ya nos estalló.',
                    ],
                ],
            ],
            [
                'title' => 'Censura a Espinoza: ¿la Asamblea fiscaliza o toma el Ministerio?',
                'category' => 'politica',
                'cover' => 'casa-grande.jpg',
                'credit' => 'Casa Grande del Pueblo, La Paz. Wikimedia Commons / Fernando6718 (CC BY-SA 4.0).',
                'sources' => [
                    ['name' => 'El Deber — Paz destituye a Espinoza y nombra interino', 'url' => 'https://eldeber.com.bo/pais/rodrigo-paz-cambia-espinoza-mientras-espera-decision-constitucional_1787320630'],
                    ['name' => 'Infobae — la ALP censura al ministro de Economía', 'url' => 'https://www.infobae.com/america/america-latina/2026/08/19/el-congreso-de-bolivia-censuro-al-ministro-de-economia-por-no-asistir-a-una-interpelacion/'],
                ],
                'description' => 'La ALP censuró al ministro de Economía José Gabriel Espinoza (91-34-4) por no ir a la interpelación: alegó coordinación por diésel en Santa Cruz y viaje a Brasil. El 21 de agosto Rodrigo Paz lo destituyó y dejó interino a Óscar Mario Justiniano. Espinoza fue al Tribunal Constitucional. ¿La censura es control democrático, o un Legislativo que usa la crisis para descabezar al gabinete?',
                'thread' => [
                    [
                        'nick' => 'redaccion',
                        'text' => 'Primer ministro censurado del gobierno de Paz. El Ejecutivo tardó, el ministro se ausentó, la ALP votó. ¿Fiscalización o golpe de recinto?',
                    ],
                    [
                        'nick' => 'uni_cbba',
                        'text' => 'Un ministro de Economía que no se sienta a explicar la crisis del diésel le está diciendo al país que el Congreso estorba. La interpelación no es opcional.',
                        'replies' => [
                            [
                                'nick' => 'paceno_sur',
                                'text' => 'También es cierto que lo citan cuando está apagando incendios. Si cada ministro cae por no ir, vamos a tener gabinete rotativo y cero política económica.',
                            ],
                        ],
                    ],
                    [
                        'nick' => 'joven_scr',
                        'text' => 'Me importa menos el nombre que el patrón: se censura, se ampara, se nombra interino al de Producción. El cargo se recicla entre los mismos. Eso es Viejocracia de organigrama.',
                    ],
                    [
                        'nick' => 'sindical_lpz',
                        'text' => 'Espinoza ya se había perdido otra citación por el FMI. No es un accidente: es desprecio. Que rinda cuentas o que se vaya. El pueblo no puede “viajar a Brasil”.',
                    ],
                    [
                        'nick' => 'crucena_prod',
                        'text' => 'Si lo sacan y el diésel sigue igual, fue teatro. Quiero ver qué hace Justiniano con el 5676, no otro comunicado de “continuidad del servicio”.',
                    ],
                    [
                        'nick' => 'docente_oruro',
                        'text' => 'El TC no debería ser el plan B de todo ministro censurado. Si la SCP 20/2023 dice que hay 24 horas para destituir, se cumple. Punto. Después que litiguen.',
                    ],
                ],
            ],
            [
                'title' => 'Caso Cerimedo–Beller: ¿gobierno invisible o denuncia sin pruebas?',
                'category' => 'politica',
                'cover' => 'ciudad-bo.jpg',
                'credit' => 'Ciudad boliviana. Unsplash (foto de La Paz, Bolivia).',
                'sources' => [
                    ['name' => 'La Nación — acusaciones de Beller contra Cerimedo', 'url' => 'https://www.lanacion.com.ar/politica/las-acusaciones-de-nadia-beller-contra-fernando-cerimedo-cajones-con-billetes-negociados-y-una-elite-nid18082026/'],
                    ['name' => 'teleSUR — denuncia e entorno presidencial', 'url' => 'https://www.telesurtv.net/bolivia-beller-red-corrupcion-presidencial/'],
                    ['name' => 'eju.tv — el colapso del “gobierno invisible”', 'url' => 'https://eju.tv/2026/08/el-colapso-del-gobierno-invisible/'],
                ],
                'description' => 'Tras el atentado contra Nadia Beller en Santa Cruz, la abogada acusó a Fernando Cerimedo —asesor del entorno presidencial, sin planilla estatal clara— de negociados con combustibles, oro y operadores privados, e involucró al círculo de Rodrigo Paz. El Gobierno pidió pruebas y dijo que colaborará. El caso mezcla tentativa de feminicidio, audios, traders y la crisis del diésel. ¿Se investiga todo, o se tapa porque quema?',
                'thread' => [
                    [
                        'nick' => 'redaccion',
                        'text' => 'Hay un hecho de violencia gravísimo y hay denuncias de corrupción que todavía se tienen que probar. Se pueden exigir las dos investigaciones. No las mezclen para anularse.',
                    ],
                    [
                        'nick' => 'madre_elalto',
                        'text' => 'Una mujer baleada denuncia al operador del poder. Si el Estado responde “traiga pruebas” y no resguarda, ya sabemos de qué lado está. Primero la vida, después el organigrama.',
                        'replies' => [
                            [
                                'nick' => 'paceno_sur',
                                'text' => 'Resguardo sí. Condena mediática al Presidente sin expediente, no. En este país una entrevista de hospital ya vale como sentencia.',
                            ],
                        ],
                    ],
                    [
                        'nick' => 'vecino_lpz',
                        'text' => 'Lo que más me jode: un asesor que “no está en planilla” pero mueve combustibles y puertas. Eso es el gobierno invisible. Si Paz no lo cortó, es su responsabilidad política aunque el juez todavía no hable.',
                    ],
                    [
                        'nick' => 'yungas_verde',
                        'text' => 'Los audios con YPFB y traders, si existen, tienen que peritarse y publicarse con cadena de custodia. Si no existen, alguien está dinamitando el país con un pendrive.',
                    ],
                    [
                        'nick' => 'comerciante_tz',
                        'text' => 'En Tarija el rumor ya es mercado. Baja la confianza, sube el dólar paralelo, y el almacén cierra más temprano. La política sucia se paga en la feria, no en el palacio.',
                    ],
                    [
                        'nick' => 'joven_scr',
                        'text' => 'Investigación sin candado político: Cerimedo, cupos, oro, sobresueldos. El cortafuegos institucional o esto se come al gobierno y a la oposición juntos.',
                    ],
                ],
            ],
            [
                'title' => 'Estado de excepción hasta octubre: ¿orden o miedo a la calle?',
                'category' => 'seguridad',
                'cover' => 'calle-lpz.jpg',
                'credit' => 'Calle en La Paz, Bolivia. Unsplash.',
                'sources' => [
                    ['name' => 'AP News — bloqueos, estado de excepción y crisis de combustibles', 'url' => 'https://apnews.com/article/bolivia-rodrigo-paz-crisis-energetica-subsidios-crisis-economica-7c3d07fa22adc934f381827716c5e6b6'],
                    ['name' => 'La Política Online — excepción y gobernabilidad', 'url' => 'https://www.lapoliticaonline.com/amp/354051-el-caso-cerimedo-complica-al-gobierno-de-paz-en-bolivia/'],
                ],
                'description' => 'Tras 53 días de bloqueos por precios y combustibles, el gobierno de Paz decretó estado de excepción para frenar protestas, con vigencia hacia octubre. Sindicatos hablan de criminalizar la demanda; el Ejecutivo, de asfixia a las ciudades. ¿Se puede gobernar una crisis energética con excepción permanente, o se está normalizando el recorte de derechos?',
                'thread' => [
                    [
                        'nick' => 'redaccion',
                        'text' => 'La excepción ya no es “un fin de semana de emergencia”. Es el marco de la política cotidiana. ¿Hasta cuándo?',
                    ],
                    [
                        'nick' => 'sindical_lpz',
                        'text' => 'Nos cortan el derecho a protestar porque el diésel no llega. Es al revés: protestamos porque el Estado no cumple. La excepción es el palo para no escuchar.',
                        'replies' => [
                            [
                                'nick' => 'madre_elalto',
                                'text' => 'Hermano, 53 días de bloqueo también es violencia contra el que trabaja. Mi hijo no llegó a la escuela. El sindicato no es dueño de la carretera.',
                            ],
                            [
                                'nick' => 'sindical_lpz',
                                'text' => 'Sin carretera no nos ven. Con excepción tampoco. Entonces ¿cuál es el canal? ¿Twitter del ministro?',
                            ],
                        ],
                    ],
                    [
                        'nick' => 'chofer_scz',
                        'text' => 'Yo perdí dos semanas parado. Ni el bloqueo me dio diésel ni la excepción me llenó el tanque. Las dos recetas fracasaron para el que vive del volante.',
                    ],
                    [
                        'nick' => 'docente_oruro',
                        'text' => 'Un gobierno joven que gobierna con estado de excepción es una contradicción. Se puede ser nuevo y viejócrata: el palo es el método de siempre.',
                    ],
                    [
                        'nick' => 'paceno_sur',
                        'text' => 'Sin orden no hay reforma. El 5676 no se aplica si cada sector toma una tranca. El problema es usarlo para callar periodistas y opositores, no para abrir rutas.',
                    ],
                ],
            ],
            [
                'title' => '¿Deben los legisladores levantar el secreto bancario —y el de su familia?',
                'category' => 'politica',
                'cover' => 'asamblea-plaza.jpg',
                'credit' => 'Asamblea Legislativa Plurinacional, Plaza Murillo, La Paz. Wikimedia Commons / Yastay (CC BY-SA 4.0).',
                'sources' => [
                    ['name' => 'eju.tv — pedido de secreto bancario y pegas estatales', 'url' => 'https://eju.tv/2026/08/sobresueldos-libre-pide-a-legisladores-y-autoridades-del-gobierno-renunciar-al-secreto-bancario-y-pdc-investigar-pagos/'],
                    ['name' => 'Éxito Noticias — diputados piden pruebas y abrir cuentas', 'url' => 'https://exitonoticias.com.bo/tras-ser-mencionados-en-la-lista-de-beller-diputados-exigen-pruebas-y-solicitan-levantar-el-secreto-bancario/400908/'],
                ],
                'description' => 'Tras la lista de Beller, diputados de Libre pidieron que asambleístas y ministros renuncien al secreto bancario, incluso de familiares de primer grado, y que se investiguen “pegas” en empresas públicas del oriente. Otros dicen que sin prueba previa es un show y un riesgo para terceros. ¿Transparencia total del cargo público, o cacería?',
                'thread' => [
                    [
                        'nick' => 'redaccion',
                        'text' => 'La propuesta ya está en el recinto: cuentas de legisladores y de su entorno. ¿Línea roja democrática o el mínimo ético del cargo?',
                    ],
                    [
                        'nick' => 'joven_scr',
                        'text' => 'Si manejás presupuesto y leyes, tu cuenta no es intimidad: es conflicto de interés permanente. Familia de primer grado incluida. El que se ofende, que se vaya a la privada.',
                        'replies' => [
                            [
                                'nick' => 'uni_cbba',
                                'text' => 'Cuidado con el cónyuge que tiene empresa propia de hace 20 años. Hay que acotar: movimientos atípicos, no espiar la vida. Si no, mañana el gobierno de turno usa esto contra la bancada que estorba.',
                            ],
                        ],
                    ],
                    [
                        'nick' => 'mineria_potosi',
                        'text' => 'En Potosí hemos visto diputados pobres de discurso y ricos de camioneta. El secreto bancario es el candado de esa magia. Que lo abran.',
                    ],
                    [
                        'nick' => 'crucena_prod',
                        'text' => 'Que investiguen también las directorios de empresas públicas. El sobresueldo no es solo sobre. A veces es el cuñado en ENDE.',
                    ],
                    [
                        'nick' => 'paceno_sur',
                        'text' => 'Apoyo transparencia. Rechazo linchamiento por PDF anónimo. Orden: comisión, peritos, plazos, nombres con cargo y monto, no un Excel de WhatsApp.',
                    ],
                ],
            ],
            [
                'title' => 'Filas, cupos y YPFB: ¿quién decide quién carga gasolina?',
                'category' => 'infraestructura',
                'cover' => 'ypfb-camiri.jpg',
                'credit' => 'Torre YPFB, Camiri, Santa Cruz. Wikimedia Commons / Ariela2020 (CC BY-SA 4.0).',
                'sources' => [
                    ['name' => 'eju.tv — cupos de combustible y operadores privados', 'url' => 'https://eju.tv/2026/08/el-colapso-del-gobierno-invisible/'],
                    ['name' => 'EFE — filas en estaciones y decreto de grandes consumidores', 'url' => 'https://efe.com/economia/2026-08-17/bolivia-subvencion-precio-diesel-consumidores-combustible/'],
                ],
                'description' => 'Las estaciones volvieron a las filas. Se denuncia un presunto cobro informal por litro para asignar cupos de gasolina y diésel, y operadores privados en la cadena de importación. YPFB y el Ministerio hablan de abastecimiento urgente; la gente habla de privilegiados que cargan de noche. ¿Crisis logística, o mercado paralelo con sello estatal?',
                'thread' => [
                    [
                        'nick' => 'redaccion',
                        'text' => 'Pregunta simple: si el precio “social” se mantiene para el particular, ¿por qué el tanque sigue vacío? Cupos, logística o privilegio.',
                    ],
                    [
                        'nick' => 'chofer_scz',
                        'text' => 'Hay estaciones donde el particular hace 6 horas y pasa un camión con “autorización”. Eso no es escasez: es ventanilla. Publiquen el cupo por surtidor, hora por hora.',
                        'replies' => [
                            [
                                'nick' => 'agro_beni',
                                'text' => 'En el norte pasa igual con el diésel agrícola. El que tiene contacto carga; el que tiene chaco espera. Así se pudre el país.',
                            ],
                        ],
                    ],
                    [
                        'nick' => 'comerciante_tz',
                        'text' => 'El flete ya me come el margen. Si hay traders cobrando comisión por litro, eso es un impuesto clandestino. Fiscalía y YPFB tienen que mostrar contratos, no eslóganes.',
                    ],
                    [
                        'nick' => 'vecino_lpz',
                        'text' => 'Plataforma pública de cupos. Si el Estado no puede mostrar quién carga, entonces el Estado no está administrando el combustible: lo está repartiendo.',
                    ],
                    [
                        'nick' => 'sindical_lpz',
                        'text' => 'También hay acaparamiento y reventa. No todo es palacio. Hay surtidor que esconde y hay sindicalista que negocia. Limpieza de los dos lados.',
                    ],
                    [
                        'nick' => 'yungas_verde',
                        'text' => 'Mientras el oro y las reservas paguen el litro barato para algunos, el resto va a seguir en cola. El cupo es la nueva aduana interna.',
                    ],
                ],
            ],
            [
                'title' => 'Oro, reservas y traders: ¿se está vendiendo el colchón del país?',
                'category' => 'economia',
                'cover' => 'cerro-rico.jpg',
                'credit' => 'Cerro Rico, Potosí. Wikimedia Commons / Christophe Meneboeuf (CC BY-SA 2.5).',
                'sources' => [
                    ['name' => 'La Nación — denuncias de negociados con oro y combustibles', 'url' => 'https://www.lanacion.com.ar/politica/las-acusaciones-de-nadia-beller-contra-fernando-cerimedo-cajones-con-billetes-negociados-y-una-elite-nid18082026/'],
                    ['name' => 'eju.tv — oro, cupos y el entorno del poder', 'url' => 'https://eju.tv/2026/08/el-colapso-del-gobierno-invisible/'],
                ],
                'description' => 'El Gobierno atribuye años de subvención de combustibles a reservas e incluso a oro. Las denuncias de agosto hablan de negociados con metal y con la importación de carburantes. El BCB no es un kiosco: cada lingote que se va para tapar el tanque es política monetaria. ¿Emergencia inevitable, o saqueo con justificativo técnico?',
                'thread' => [
                    [
                        'nick' => 'redaccion',
                        'text' => 'El hilo no es contra el minero. Es contra usar el metal del país para sostener un precio político hasta que ya no hay metal ni precio.',
                    ],
                    [
                        'nick' => 'mineria_potosi',
                        'text' => 'En Potosí sacamos el mineral y La Paz decide el destino. Si el oro se va a pagar diésel de industria y comisión de trader, que lo digan con cifra. No con “es por el pueblo”.',
                        'replies' => [
                            [
                                'nick' => 'joven_scr',
                                'text' => 'Eso. Auditoría de reservas, de ventas y de intermediarios. Si hay comisión de un boliviano por litro, eso es más rentable que cualquier mina chica.',
                            ],
                        ],
                    ],
                    [
                        'nick' => 'docente_oruro',
                        'text' => 'Quemar reservas para no tocar el precio es la receta de siempre. Después viene el “no hay dólares” y la culpa es del ciudadano que “especula”. No. La cuenta la hizo el Estado.',
                    ],
                    [
                        'nick' => 'crucena_prod',
                        'text' => 'Sin dólares no hay diésel importado. Sin diésel no hay agro. El círculo es real. Lo que no puede ser real es que el círculo lo administren amigos del entorno.',
                    ],
                    [
                        'nick' => 'paceno_sur',
                        'text' => 'Pidamos informes del BCB en lenguaje humano: cuánto oro, a quién, a qué precio, para qué factura de combustible. Si eso es secreto, el secreto ya es el problema.',
                    ],
                ],
            ],
            [
                'title' => 'Pegas en empresas públicas: ¿el cargo es mérito o apellido?',
                'category' => 'politica',
                'cover' => 'teleferico-unsplash.jpg',
                'credit' => 'Mi Teleférico, La Paz. Unsplash.',
                'sources' => [
                    ['name' => 'eju.tv — investigación de pegas y familiares en empresas del Estado', 'url' => 'https://eju.tv/2026/08/sobresueldos-libre-pide-a-legisladores-y-autoridades-del-gobierno-renunciar-al-secreto-bancario-y-pdc-investigar-pagos/'],
                ],
                'description' => 'En el mismo paquete de denuncias de agosto se pidió investigar directorios y puestos en empresas del Estado —ENDE y otras del oriente— donde aparecerían familiares de parlamentarios. No es un escándalo nuevo: es el software viejo. ¿Se puede hablar de recambio si el organigrama sigue siendo un árbol genealógico?',
                'thread' => [
                    [
                        'nick' => 'redaccion',
                        'text' => 'El hilo de Viejocracia entra acá de lleno: no es la edad del director, es si el cargo se hereda. Nombres, hojas de vida y fechas de ingreso, no rumor.',
                    ],
                    [
                        'nick' => 'joven_scr',
                        'text' => 'Un país con mediana de 25 años y directorios de siempre. La cuota de renovación no es capricho: es oxigenar. Si tu cónyuge entra a ENDE la semana que votás una ley, eso no es coincidencia.',
                        'replies' => [
                            [
                                'nick' => 'sindical_lpz',
                                'text' => 'En las empresas del Estado también hay trabajador de base que no es cuñado de nadie. No usen esto para tercerizar y echar gente. Apunten al directorio, no al electricista.',
                            ],
                        ],
                    ],
                    [
                        'nick' => 'uni_cbba',
                        'text' => 'Hoja de vida pública de todo director de empresa estatal. Título, experiencia, parentesco con asambleístas. Si duele, es que funciona.',
                    ],
                    [
                        'nick' => 'agro_beni',
                        'text' => 'Mientras el chaco no tiene diésel, hay gente cobrando en directorio por “representar al área rural”. Representen trayendo combustible, no trayendo al primo.',
                    ],
                    [
                        'nick' => 'madre_elalto',
                        'text' => 'Mi hija egresó y no hay pega. El hijo del dirigente sí. Eso es la Viejocracia cotidiana, no un paper. Basta.',
                    ],
                ],
            ],
            [
                'title' => 'Gobernabilidad 2026: ¿Paz termina el mandato o el país se va a revocatorio?',
                'category' => 'politica',
                'cover' => 'la-paz.jpg',
                'credit' => 'La Paz, Bolivia. Unsplash.',
                'sources' => [
                    ['name' => 'La Política Online — crisis de gobernabilidad y escenario de revocatorio', 'url' => 'https://www.lapoliticaonline.com/amp/354051-el-caso-cerimedo-complica-al-gobierno-de-paz-en-bolivia/'],
                    ['name' => 'AP News — nueve meses de gobierno, filas y excepción', 'url' => 'https://apnews.com/article/bolivia-rodrigo-paz-crisis-energetica-subsidios-crisis-economica-7c3d07fa22adc934f381827716c5e6b6'],
                ],
                'description' => 'Nueve meses de gobierno, excepción, censura ministerial, filas de combustible y una denuncia que apunta al entorno presidencial. Hay quienes hablan de adelantar elecciones o empujar un revocatorio después del ciclo electoral. Otros dicen que destituir a cada presidente en crisis es el verdadero atraso. ¿Se aguanta y se investiga, o se abre otro ciclo de palacio?',
                'thread' => [
                    [
                        'nick' => 'redaccion',
                        'text' => 'Última pregunta del tablero: ¿la salida es institucional (investigar, transparentar, reformar el diésel) o es otra vez tumbar al que está sentado?',
                    ],
                    [
                        'nick' => 'paceno_sur',
                        'text' => 'Revocatorio ahora es echar nafta al tanque vacío. Primero investigación, cupos públicos y un ministro de Economía que vaya al Congreso. Si en 90 días no hay nada, hablamos de urnas.',
                        'replies' => [
                            [
                                'nick' => 'joven_scr',
                                'text' => '90 días es una eternidad cuando no hay diésel. El reloj lo pone la fila, no el calendario de La Paz.',
                            ],
                        ],
                    ],
                    [
                        'nick' => 'sindical_lpz',
                        'text' => 'Este gobierno pidió paciencia y mandó excepción. La calle no va a esperar 2028. Si no hay diésel y hay operadores invisibles, el mandato ya está vacío.',
                    ],
                    [
                        'nick' => 'crucena_prod',
                        'text' => 'Cambiar de presidente cada crisis es la costumbre que nos quebró. Quiero ver si Paz corta a su entorno. Si no corta, se cae solo. Si corta, tal vez gobierna.',
                    ],
                    [
                        'nick' => 'docente_oruro',
                        'text' => 'La Viejocracia también es creer que el país se arregla con otro nombre en el palacio y el mismo método. Recambio de práctica, no solo de foto.',
                    ],
                    [
                        'nick' => 'vecino_lpz',
                        'text' => 'Investiguen ya: sobresueldos, cupos, oro, Cerimedo. Publiquen. El que esté limpio sigue. El que no, presidio. Sin eso, cualquier revocatorio es solo otra pelea de cúpulas.',
                    ],
                ],
            ],
        ];
    }

    private function seedVoteTopics(): void
    {
        $author = $this->voices['redaccion'];

        $topics = [
            [
                'title' => 'Comisión independiente para investigar presuntos sobresueldos',
                'description' => 'Fuera de la ALP: peritos, secreto bancario voluntario de asambleístas y informe público en 60 días.',
                'target' => 8000,
                'count' => 2140,
            ],
            [
                'title' => 'Cupos de diésel y gasolina en tiempo real',
                'description' => 'YPFB debe publicar estación, volumen y tipo de cliente. Sin padrón a oscuras no hay subvención creíble.',
                'target' => 10000,
                'count' => 4310,
            ],
            [
                'title' => 'Levantar el secreto bancario de legisladores en ejercicio',
                'description' => 'Cuentas propias y de familiares de primer grado mientras dure el mandato. El cargo no es intimidad.',
                'target' => 6500,
                'count' => 1980,
            ],
            [
                'title' => 'Derogar el estado de excepción y abrir mesa de combustibles',
                'description' => 'Protesta legal, rutas abiertas y un cronograma público de abastecimiento. La excepción no puede ser el plan económico.',
                'target' => 5000,
                'count' => 1260,
            ],
        ];

        foreach ($topics as $i => $topic) {
            VoteTopic::factory()->for($author, 'author')->create([
                'title' => $topic['title'],
                'description' => $topic['description'],
                'target_signatures' => $topic['target'],
                'signatures_count' => $topic['count'],
                'deadline' => now()->addDays(40 - $i * 4),
                'created_at' => now()->subDays(8 - $i),
            ]);
        }
    }
}
