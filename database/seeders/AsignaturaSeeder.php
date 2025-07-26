<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asignatura;
use App\Models\Carrera;

class AsignaturaSeeder extends Seeder
{
    public function run()
    {
        $asignaturas = [
            // DESARROLLO DE SOFTWARE (DS)
            [
                'carrera_codigo' => 'DS',
                'asignaturas' => [
                    ['nombre' => 'FUNDAMENTOS DE PROGRAMACIÓN', 'codigo' => 'DS-FP-01', 'semestre' => 1, 'horas_semanales' => 7, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'MATEMÁTICA DISCRETA', 'codigo' => 'DS-MD-01', 'semestre' => 1, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'OFIMÁTICA', 'codigo' => 'DS-OF-01', 'semestre' => 1, 'horas_semanales' => 2, 'horas_practicas' => 1, 'creditos' => 2],
                    ['nombre' => 'ANÁLISIS Y DISEÑO DE SISTEMAS', 'codigo' => 'DS-ADS-01', 'semestre' => 1, 'horas_semanales' => 7, 'horas_practicas' => 3, 'creditos' => 4],

                    ['nombre' => 'PROGRAMACIÓN ORIENTADA A OBJETOS', 'codigo' => 'DS-POO-02', 'semestre' => 2, 'horas_semanales' => 7, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'BASE DE DATOS', 'codigo' => 'DS-BD-02', 'semestre' => 2, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'ESTADÍSTICA DESCRIPTIVA', 'codigo' => 'DS-ED-02', 'semestre' => 2, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'REALIDAD SOCIO ECONÓMICO CULTURAL Y AMBIENTAL', 'codigo' => 'DS-RSECA-02', 'semestre' => 2, 'horas_semanales' => 2, 'horas_practicas' => 1, 'creditos' => 2],
                    ['nombre' => 'METODOLOGÍAS DE DESARROLLO DE SISTEMAS', 'codigo' => 'DS-MDS-02', 'semestre' => 2, 'horas_semanales' => 7, 'horas_practicas' => 3, 'creditos' => 4],

                    ['nombre' => 'PROGRAMACIÓN MÓVIL', 'codigo' => 'DS-PM-03', 'semestre' => 3, 'horas_semanales' => 7, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'PROGRAMACIÓN WEB', 'codigo' => 'DS-PW-03', 'semestre' => 3, 'horas_semanales' => 7, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'FUNDAMENTOS DE REDES Y TELECOMUNICACIONES', 'codigo' => 'DS-FRT-03', 'semestre' => 3, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'DISEÑO DE INTERFAZ', 'codigo' => 'DS-DI-03', 'semestre' => 3, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'EXPRESIÓN ORAL Y ESCRITA', 'codigo' => 'DS-EOE-03', 'semestre' => 3, 'horas_semanales' => 2, 'horas_practicas' => 1, 'creditos' => 2]
                ]
            ],

            // TECNOLOGÍA SUPERIOR EN DESARROLLO DE SOFTWARE (TSDS)
            [
                'carrera_codigo' => 'TSDS',
                'asignaturas' => [
                    ['nombre' => 'INGLÉS B1.2 (Specific purpose)', 'codigo' => 'TSDS-ING-04', 'semestre' => 4, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'FUNDAMENTOS DE REDES Y CONECTIVIDAD', 'codigo' => 'TSDS-FRC-05', 'semestre' => 5, 'horas_semanales' => 7, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'CALIDAD DE SOFTWARE', 'codigo' => 'TSDS-CS-05', 'semestre' => 5, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'LEGISLACIÓN INFORMÁTICA', 'codigo' => 'TSDS-LI-04', 'semestre' => 4, 'horas_semanales' => 4, 'horas_practicas' => 0, 'creditos' => 3],
                    ['nombre' => 'DIVERSIDAD Y CULTURA', 'codigo' => 'TSDS-DC-04', 'semestre' => 4, 'horas_semanales' => 2, 'horas_practicas' => 0, 'creditos' => 2],
                    ['nombre' => 'BASE DE DATOS AVANZADA', 'codigo' => 'TSDS-BDA-02', 'semestre' => 2, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'PROGRAMACIÓN DE APLICACIONES WEB', 'codigo' => 'TSDS-PAW-04', 'semestre' => 4, 'horas_semanales' => 8, 'horas_practicas' => 4, 'creditos' => 5],
                    ['nombre' => 'DESARROLLO DE APLICACIONES MÓVILES', 'codigo' => 'TSDS-DAM-04', 'semestre' => 4, 'horas_semanales' => 7, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'TENDENCIAS ACTUALES DE PROGRAMACIÓN', 'codigo' => 'TSDS-TAP-05', 'semestre' => 5, 'horas_semanales' => 9, 'horas_practicas' => 5, 'creditos' => 5],
                    ['nombre' => 'ESTADÍSTICA DESCRIPTIVA', 'codigo' => 'TSDS-ED-04', 'semestre' => 4, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'EMPRENDIMIENTO', 'codigo' => 'TSDS-EMP-05', 'semestre' => 5, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'ÉTICA PRESENCIAL', 'codigo' => 'TSDS-EP-05', 'semestre' => 5, 'horas_semanales' => 2, 'horas_practicas' => 0, 'creditos' => 2],
                    ['nombre' => 'PROYECTO DE TITULACIÓN', 'codigo' => 'TSDS-PT-05', 'semestre' => 5, 'horas_semanales' => 2, 'horas_practicas' => 0, 'creditos' => 5]
                ]
            ],

            // RIEGO Y PRODUCCIÓN AGRÍCOLA (RPA)
            [
                'carrera_codigo' => 'RPA',
                'asignaturas' => [
                    ['nombre' => 'MAQUINARIAS Y EQUIPOS AGRÍCOLAS', 'codigo' => 'RPA-MEA-02', 'semestre' => 2, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'DISEÑO AGRONÓMICO DE SISTEMA DE RIEGO', 'codigo' => 'RPA-DASR-02', 'semestre' => 2, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'FISIOLOGÍA VEGETAL', 'codigo' => 'RPA-FV-02', 'semestre' => 2, 'horas_semanales' => 7, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'TOPOGRAFÍA AGRÍCOLA', 'codigo' => 'RPA-TA-02', 'semestre' => 2, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'AGRICULTURA DE PRECISIÓN', 'codigo' => 'RPA-AP-05', 'semestre' => 5, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'FERTIRRIGACIÓN', 'codigo' => 'RPA-FERT-05', 'semestre' => 5, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'OPERACIÓN Y MANTENIMIENTO DE SISTEMAS DE RIEGO', 'codigo' => 'RPA-OMSR-05', 'semestre' => 5, 'horas_semanales' => 4, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'ADMINISTRACIÓN AGRÍCOLA', 'codigo' => 'RPA-AA-05', 'semestre' => 5, 'horas_semanales' => 2, 'horas_practicas' => 0, 'creditos' => 2],
                    ['nombre' => 'EMPRENDIMIENTO Y ASOCIATIVIDAD', 'codigo' => 'RPA-EA-05', 'semestre' => 5, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'SEMINARIO DE TITULACIÓN', 'codigo' => 'RPA-ST-05', 'semestre' => 5, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4]
                ]
            ],

            // PRODUCCIÓN PECUARIA (PP)
            [
                'carrera_codigo' => 'PP',
                'asignaturas' => [
                    ['nombre' => 'CONSTRUCCIONES RURALES', 'codigo' => 'PP-CR-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'MEJORAMIENTO GENÉTICO', 'codigo' => 'PP-MG-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'BIOESTADÍSTICA Y DISEÑO EXPERIMENTAL', 'codigo' => 'PP-BDE-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'REPRODUCCIÓN ANIMAL', 'codigo' => 'PP-RA-04', 'semestre' => 4, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'ENFERMERÍA VETERINARIA', 'codigo' => 'PP-EV-04', 'semestre' => 4, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'LEGISLACIÓN PECUARIA', 'codigo' => 'PP-LP-04', 'semestre' => 4, 'horas_semanales' => 2, 'horas_practicas' => 0, 'creditos' => 2],
                    ['nombre' => 'BOVINOTECNIA', 'codigo' => 'PP-BOV-05', 'semestre' => 5, 'horas_semanales' => 3, 'horas_practicas' => 0, 'creditos' => 3],
                    ['nombre' => 'BOVINOTECNIA', 'codigo' => 'PP-BOV-02', 'semestre' => 2, 'horas_semanales' => 1, 'horas_practicas' => 0, 'creditos' => 1],
                    ['nombre' => 'AVICULTURA', 'codigo' => 'PP-AVI-05', 'semestre' => 5, 'horas_semanales' => 3, 'horas_practicas' => 0, 'creditos' => 3],
                    ['nombre' => 'PORCINOTECNIA', 'codigo' => 'PP-POR-05', 'semestre' => 5, 'horas_semanales' => 3, 'horas_practicas' => 0, 'creditos' => 3],
                    ['nombre' => 'ADMINISTRACIÓN Y ECONOMÍA PECUARIA', 'codigo' => 'PP-AEP-05', 'semestre' => 5, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'PROYECTOS SOCIOECONÓMICOS PECUARIOS', 'codigo' => 'PP-PSP-05', 'semestre' => 5, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'TRABAJO DE TITULACIÓN', 'codigo' => 'PP-TT-05', 'semestre' => 5, 'horas_semanales' => 2, 'horas_practicas' => 2, 'creditos' => 4]
                ]
            ],

            // MECÁNICA AUTOMOTRIZ (MA)
            [
                'carrera_codigo' => 'MA',
                'asignaturas' => [
                    ['nombre' => 'MECANICA DE PATIO', 'codigo' => 'MA-MP-01', 'semestre' => 1, 'horas_semanales' => 7, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'MOTORES DE COMBUSTIÓN INTERNA', 'codigo' => 'MA-MCI-01', 'semestre' => 1, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'METROLOGÍA', 'codigo' => 'MA-MET-01', 'semestre' => 1, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'SEGURIDAD, HIGIENE Y GESTIÓN AMBIENTAL', 'codigo' => 'MA-SHGA-01', 'semestre' => 1, 'horas_semanales' => 4, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'OFIMÁTICA', 'codigo' => 'MA-OF-01', 'semestre' => 1, 'horas_semanales' => 2, 'horas_practicas' => 1, 'creditos' => 2],

                    ['nombre' => 'MANTENIMIENTO Y REPARACIÓN DE MOTORES', 'codigo' => 'MA-MRM-02', 'semestre' => 2, 'horas_semanales' => 8, 'horas_practicas' => 3, 'creditos' => 5],
                    ['nombre' => 'LUBRICANTES Y COMBUSTIBLES', 'codigo' => 'MA-LC-02', 'semestre' => 2, 'horas_semanales' => 4, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'DIBUJO ASISTIDO POR COMPUTADORA', 'codigo' => 'MA-DAC-02', 'semestre' => 2, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'EMPRENDIMIENTO', 'codigo' => 'MA-EMP-02', 'semestre' => 2, 'horas_semanales' => 2, 'horas_practicas' => 1, 'creditos' => 2],

                    ['nombre' => 'ESTRUCTURAS Y ACABADOS AUTOMOTRICES', 'codigo' => 'MA-EAA-03', 'semestre' => 3, 'horas_semanales' => 4, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'HIDRÁULICA Y NEUMÁTICA', 'codigo' => 'MA-HN-03', 'semestre' => 3, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'NUEVAS TECNOLOGÍAS', 'codigo' => 'MA-NT-03', 'semestre' => 3, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'AUTOTRÓNICA', 'codigo' => 'MA-AUTO-03', 'semestre' => 3, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'MANTENIMIENTO Y REPARACIÓN DE MOTORES', 'codigo' => 'MA-MRM-03', 'semestre' => 3, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 3],

                    ['nombre' => 'SISTEMAS DE INYECCIÓN AUTOMOTRIZ', 'codigo' => 'MA-SIA-04', 'semestre' => 4, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'TRANSMISIONES AUTOMÁTICA', 'codigo' => 'MA-TA-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'MAQUINARIA PESADA', 'codigo' => 'MA-MP-04', 'semestre' => 4, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'DISEÑO DE PROYECTOS', 'codigo' => 'MA-DP-04', 'semestre' => 4, 'horas_semanales' => 4, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'SISTEMAS DE PROPULSIÓN ELÉCTRICA', 'codigo' => 'MA-SPE-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],

                    ['nombre' => 'SISTEMAS DE INYECCIÓN A DIESEL', 'codigo' => 'MA-SIAD-05', 'semestre' => 5, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'SOFTWARE AUTOMOTRIZ', 'codigo' => 'MA-SA-05', 'semestre' => 5, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'CONTROL TÉCNICO VEHICULAR', 'codigo' => 'MA-CTV-05', 'semestre' => 5, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'ADMINISTRACIÓN Y ORGANIZACIÓN DE TALLERES', 'codigo' => 'MA-AOT-05', 'semestre' => 5, 'horas_semanales' => 1, 'horas_practicas' => 0, 'creditos' => 1],
                    ['nombre' => 'TRABAJO DE TITULACIÓN', 'codigo' => 'MA-TT-05', 'semestre' => 5, 'horas_semanales' => 3, 'horas_practicas' => 0, 'creditos' => 5]
                ]
            ],

            // ELECTRICIDAD (ELE)
            [
                'carrera_codigo' => 'ELE',
                'asignaturas' => [
                    ['nombre' => 'ELECTROTÉCNIA', 'codigo' => 'ELE-ET-01', 'semestre' => 1, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'MATEMÁTICA TÉCNICA', 'codigo' => 'ELE-MT-01', 'semestre' => 1, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'OFIMÁTICA', 'codigo' => 'ELE-OF-01', 'semestre' => 1, 'horas_semanales' => 3, 'horas_practicas' => 2, 'creditos' => 2],
                    ['nombre' => 'ELECTRÓNICA', 'codigo' => 'ELE-ELEC-01', 'semestre' => 1, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'SEGURIDAD INDUSTRIAL', 'codigo' => 'ELE-SI-01', 'semestre' => 1, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],

                    ['nombre' => 'CIRCUITOS ELÉCTRICOS', 'codigo' => 'ELE-CE-02', 'semestre' => 2, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'MAQUINAS ELÉCTRICAS', 'codigo' => 'ELE-ME-02', 'semestre' => 2, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'INSTALACIONES ELÉCTRICAS', 'codigo' => 'ELE-IE-02', 'semestre' => 2, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'INSTRUMENTACIÓN INDUSTRIAL', 'codigo' => 'ELE-II-02', 'semestre' => 2, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'ELECTRÓNICA DE POTENCIA', 'codigo' => 'ELE-EP-02', 'semestre' => 2, 'horas_semanales' => 4, 'horas_practicas' => 2, 'creditos' => 3],

                    ['nombre' => 'SISTEMAS DE GENERACIÓN Y TRANSMISIÓN DE ENERGÍA ELÉCTRICA', 'codigo' => 'ELE-SGTEE-03', 'semestre' => 3, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'CONTROL ELÉCTRICO INDUSTRIAL', 'codigo' => 'ELE-CEI-03', 'semestre' => 3, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'PROTECCIONES Y MANTENIMIENTO ELÉCTRICO', 'codigo' => 'ELE-PME-03', 'semestre' => 3, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'ENERGÍAS ALTERNATIVAS', 'codigo' => 'ELE-EA-03', 'semestre' => 3, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'EMPRENDIMIENTO', 'codigo' => 'ELE-EMP-03', 'semestre' => 3, 'horas_semanales' => 2, 'horas_practicas' => 1, 'creditos' => 2],

                    ['nombre' => 'CONTROL ELECTRONEUMÁTICO', 'codigo' => 'ELE-CEN-04', 'semestre' => 4, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'CONTROL DE PROCESOS Y AUTOMATIZACIÓN', 'codigo' => 'ELE-CPA-04', 'semestre' => 4, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'SISTEMAS DE DISTRIBUCIÓN DE ENERGÍA ELÉCTRICA', 'codigo' => 'ELE-SDEE-04', 'semestre' => 4, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'CALIDAD DE ENERGÍA Y EFICIENCIA ENERGÉTICA', 'codigo' => 'ELE-CEEE-04', 'semestre' => 4, 'horas_semanales' => 4, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'TRABAJO DE TITULACIÓN', 'codigo' => 'ELE-TT-04', 'semestre' => 4, 'horas_semanales' => 4, 'horas_practicas' => 2, 'creditos' => 5]
                ]
            ],

            // ELECTRÓNICA (ELEC)
            [
                'carrera_codigo' => 'ELEC',
                'asignaturas' => [
                    ['nombre' => 'INSTRUMENTACIÓN', 'codigo' => 'ELEC-INST-02', 'semestre' => 2, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'MICROCONTROLADORES', 'codigo' => 'ELEC-MICRO-02', 'semestre' => 2, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'ELECTRÓNICA DE POTENCIA', 'codigo' => 'ELEC-EP-02', 'semestre' => 2, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'COMUNICACIONES ANALÓGICAS/DIGITALES', 'codigo' => 'ELEC-CAD-02', 'semestre' => 2, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'EMPRENDIMIENTO', 'codigo' => 'ELEC-EMP-02', 'semestre' => 2, 'horas_semanales' => 2, 'horas_practicas' => 1, 'creditos' => 2],

                    ['nombre' => 'REDES Y CABLEADO ESTRUCTURADO', 'codigo' => 'ELEC-RCE-03', 'semestre' => 3, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'DOMÓTICA', 'codigo' => 'ELEC-DOM-03', 'semestre' => 3, 'horas_semanales' => 4, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'MÁQUINAS ELÉCTRICAS', 'codigo' => 'ELEC-ME-03', 'semestre' => 3, 'horas_semanales' => 5, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'MANTENIMIENTO ELÉCTRICO Y ELECTRÓNICO', 'codigo' => 'ELEC-MEE-03', 'semestre' => 3, 'horas_semanales' => 5, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'CONTROL ELÉCTRICO Y NEUMÁTICO', 'codigo' => 'ELEC-CEN-03', 'semestre' => 3, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],

                    ['nombre' => 'INTERNETWORKING', 'codigo' => 'ELEC-INT-04', 'semestre' => 4, 'horas_semanales' => 5, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'COMUNICACIONES', 'codigo' => 'ELEC-COM-04', 'semestre' => 4, 'horas_semanales' => 5, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'AUTOMATIZACIÓN INDUSTRIAL', 'codigo' => 'ELEC-AI-04', 'semestre' => 4, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'ROBÓTICA Y VISIÓN ARTIFICIAL', 'codigo' => 'ELEC-RVA-04', 'semestre' => 4, 'horas_semanales' => 5, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'PROYECTO DE TITULACIÓN', 'codigo' => 'ELEC-PT-04', 'semestre' => 4, 'horas_semanales' => 4, 'horas_practicas' => 2, 'creditos' => 5]
                ]
            ],

            // CONTABILIDAD (CONT)
            [
                'carrera_codigo' => 'CONT',
                'asignaturas' => [
                    ['nombre' => 'CONTABILIDAD BÁSICA', 'codigo' => 'CONT-CB-01', 'semestre' => 1, 'horas_semanales' => 8, 'horas_practicas' => 3, 'creditos' => 5],
                    ['nombre' => 'ADMINISTRACIÓN', 'codigo' => 'CONT-ADM-01', 'semestre' => 1, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'LEGISLACIÓN LABORAL Y SOCIETARIA', 'codigo' => 'CONT-LLS-01', 'semestre' => 1, 'horas_semanales' => 6, 'horas_practicas' => 3, 'creditos' => 4],
                    ['nombre' => 'COMUNICACIÓN ORAL Y ESCRITA', 'codigo' => 'CONT-COE-01', 'semestre' => 1, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 2],
                    ['nombre' => 'OFIMÁTICA', 'codigo' => 'CONT-OF-01', 'semestre' => 1, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 2],

                    ['nombre' => 'CONTABILIDAD INTERMEDIA', 'codigo' => 'CONT-CI-02', 'semestre' => 2, 'horas_semanales' => 8, 'horas_practicas' => 2, 'creditos' => 5],
                    ['nombre' => 'TRIBUTACIÓN I', 'codigo' => 'CONT-TI-02', 'semestre' => 2, 'horas_semanales' => 6, 'horas_practicas' => 1, 'creditos' => 4],
                    ['nombre' => 'ECONOMÍA', 'codigo' => 'CONT-ECO-02', 'semestre' => 2, 'horas_semanales' => 3, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'MATEMÁTICA FINANCIERA', 'codigo' => 'CONT-MF-02', 'semestre' => 2, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'ESTADÍSTICA', 'codigo' => 'CONT-EST-02', 'semestre' => 2, 'horas_semanales' => 3, 'horas_practicas' => 2, 'creditos' => 3],

                    ['nombre' => 'CONTABILIDAD DE COSTOS', 'codigo' => 'CONT-CC-03', 'semestre' => 3, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'MICROECONOMÍA', 'codigo' => 'CONT-MICRO-03', 'semestre' => 3, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'ESTADÍSTICA', 'codigo' => 'CONT-EST-03', 'semestre' => 3, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'LEGISLACIÓN MERCANTIL Y SOCIETARIA', 'codigo' => 'CONT-LMS-03', 'semestre' => 3, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'OFIMÁTICA', 'codigo' => 'CONT-OF-03', 'semestre' => 3, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 2],
                    ['nombre' => 'COMPORTAMIENTO PROFESIONAL Y SOCIAL', 'codigo' => 'CONT-CPS-03', 'semestre' => 3, 'horas_semanales' => 1, 'horas_practicas' => 0, 'creditos' => 2],

                    ['nombre' => 'CONTABILIDAD DE INSTITUCIONES FINANCIERAS Y SEGUROS', 'codigo' => 'CONT-CIFS-04', 'semestre' => 4, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'GESTIÓN DE AUDITORÍA', 'codigo' => 'CONT-GA-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'PROYECTOS DE INVERSIÓN', 'codigo' => 'CONT-PI-04', 'semestre' => 4, 'horas_semanales' => 4, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'TRIBUTACIÓN', 'codigo' => 'CONT-TRIB-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'INFORMÁTICA APLICADA A LA CONTABILIDAD', 'codigo' => 'CONT-IAC-04', 'semestre' => 4, 'horas_semanales' => 2, 'horas_practicas' => 1, 'creditos' => 2],
                    ['nombre' => 'MACROECONOMÍA', 'codigo' => 'CONT-MACRO-04', 'semestre' => 4, 'horas_semanales' => 2, 'horas_practicas' => 1, 'creditos' => 2],

                    ['nombre' => 'AUDITORÍA FINANCIERA', 'codigo' => 'CONT-AF-05', 'semestre' => 5, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 4],
                    ['nombre' => 'ANÁLISIS FINANCIERO', 'codigo' => 'CONT-ANF-05', 'semestre' => 5, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'CONTABILIDAD SUPERIOR', 'codigo' => 'CONT-CS-05', 'semestre' => 5, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 4],
                    ['nombre' => 'PRESUPUESTO', 'codigo' => 'CONT-PRES-05', 'semestre' => 5, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'EMPRENDIMIENTO', 'codigo' => 'CONT-EMP-05', 'semestre' => 5, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'MARKETING', 'codigo' => 'CONT-MKT-05', 'semestre' => 5, 'horas_semanales' => 2, 'horas_practicas' => 0, 'creditos' => 2]
                ]
            ],

            // TECNOLOGÍA SUPERIOR EN ADMINISTRACIÓN (TSA)
            [
                'carrera_codigo' => 'TSA',
                'asignaturas' => [
                    ['nombre' => 'MÉTODOS ESTADÍSTICOS', 'codigo' => 'TSA-ME-01', 'semestre' => 1, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'FUNDAMENTOS ADMINISTRATIVOS', 'codigo' => 'TSA-FA-01', 'semestre' => 1, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'MÉTODOS CONTABLES', 'codigo' => 'TSA-MC-01', 'semestre' => 1, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'OFIMÁTICA', 'codigo' => 'TSA-OF-01', 'semestre' => 1, 'horas_semanales' => 3, 'horas_practicas' => 1, 'creditos' => 2],
                    ['nombre' => 'MATEMÁTICA APLICADA', 'codigo' => 'TSA-MA-01', 'semestre' => 1, 'horas_semanales' => 5, 'horas_practicas' => 1, 'creditos' => 3],

                    ['nombre' => 'EMPRENDIMIENTO', 'codigo' => 'TSA-EMP-03', 'semestre' => 3, 'horas_semanales' => 2, 'horas_practicas' => 1, 'creditos' => 2],
                    ['nombre' => 'INVESTIGACIÓN DE MERCADO', 'codigo' => 'TSA-IM-03', 'semestre' => 3, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'DISEÑO Y EVALUACIÓN DE PROYECTO', 'codigo' => 'TSA-DEP-03', 'semestre' => 3, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'GESTIÓN DE TALENTO HUMANO', 'codigo' => 'TSA-GTH-03', 'semestre' => 3, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],

                    ['nombre' => 'PLANIFICACIÓN ESTRATÉGICA', 'codigo' => 'TSA-PE-04', 'semestre' => 4, 'horas_semanales' => 5, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'ADMINISTRACIÓN DE LA PRODUCCIÓN', 'codigo' => 'TSA-AP-04', 'semestre' => 4, 'horas_semanales' => 6, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'PRESUPUESTOS', 'codigo' => 'TSA-PRES-04', 'semestre' => 4, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'GESTIÓN TRIBUTARIA', 'codigo' => 'TSA-GT-04', 'semestre' => 4, 'horas_semanales' => 5, 'horas_practicas' => 2, 'creditos' => 3],
                    ['nombre' => 'PROYECTO DE TITULACIÓN', 'codigo' => 'TSA-PT-04', 'semestre' => 4, 'horas_semanales' => 4, 'horas_practicas' => 0, 'creditos' => 5],

                    ['nombre' => 'PLANIFICACIÓN ESTRATÉGICA', 'codigo' => 'TSA-PE-05', 'semestre' => 5, 'horas_semanales' => 6, 'horas_practicas' => 1, 'creditos' => 4],
                    ['nombre' => 'FORMULACIÓN Y EVALUACIÓN DE PROYECTOS', 'codigo' => 'TSA-FEP-05', 'semestre' => 5, 'horas_semanales' => 7, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'LIDERAZGO Y EMPRENDIMIENTO', 'codigo' => 'TSA-LE-05', 'semestre' => 5, 'horas_semanales' => 7, 'horas_practicas' => 2, 'creditos' => 4],
                    ['nombre' => 'PROYECTO DE TITULACIÓN', 'codigo' => 'TSA-PT-05', 'semestre' => 5, 'horas_semanales' => 2, 'horas_practicas' => 0, 'creditos' => 3],
                    ['nombre' => 'COMERCIO ELECTRÓNICO', 'codigo' => 'TSA-CE-05', 'semestre' => 5, 'horas_semanales' => 7, 'horas_practicas' => 2, 'creditos' => 4]
                ]
            ],

            // CENTRO DE IDIOMAS (CI)
            [
                'carrera_codigo' => 'CI',
                'asignaturas' => [
                    ['nombre' => 'INGLÉS NIVEL A1-A2', 'codigo' => 'CI-ING-A1A2', 'semestre' => 1, 'horas_semanales' => 18, 'horas_practicas' => 3, 'creditos' => 6],
                    ['nombre' => 'INGLÉS NIVEL A2', 'codigo' => 'CI-ING-A2', 'semestre' => 2, 'horas_semanales' => 12, 'horas_practicas' => 1, 'creditos' => 4]
                ]
            ],

            // EDUCACIÓN INICIAL (EI)
            [
                'carrera_codigo' => 'EI',
                'asignaturas' => [
                    ['nombre' => 'DIDÁCTICA', 'codigo' => 'EI-DID-01', 'semestre' => 1, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'PSICOLOGÍA EVOLUTIVA DEL DESARROLLO INFANTIL', 'codigo' => 'EI-PEDI-01', 'semestre' => 1, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'POLÍTICA PÚBLICA EN DESARROLLO INFANTIL Y EDUCACIÓN INICIAL', 'codigo' => 'EI-PPDIEI-01', 'semestre' => 1, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'PEDAGOGÍA', 'codigo' => 'EI-PED-01', 'semestre' => 1, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'COMPETENCIAS DIGITALES', 'codigo' => 'EI-CD-01', 'semestre' => 1, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],

                    ['nombre' => 'DISEÑO CURRICULAR I', 'codigo' => 'EI-DC1-02', 'semestre' => 2, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'DISEÑO Y APLICACIÓN DE RECURSOS EDUCATIVOS DE EDUCACIÓN INICIAL', 'codigo' => 'EI-DAREEI-02', 'semestre' => 2, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'DIDÁCTICA INTEGRADORA: PSICOMOTRICIDAD Y EXPRESIÓN CORPORAL', 'codigo' => 'EI-DIPEC-02', 'semestre' => 2, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'NEURODESARROLLO', 'codigo' => 'EI-NEURO-02', 'semestre' => 2, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3],
                    ['nombre' => 'EVALUACIÓN EDUCATIVA EN DESARROLLO INFANTIL Y EDUCACIÓN INICIAL', 'codigo' => 'EI-EEDIEI-02', 'semestre' => 2, 'horas_semanales' => 4, 'horas_practicas' => 1, 'creditos' => 3]
                ]
            ],

            // TECNOLOGÍA SUPERIOR EN DESARROLLO INFANTIL INTEGRAL (TSDII)
            [
                'carrera_codigo' => 'TSDII',
                'asignaturas' => [
                    ['nombre' => 'CUIDANDO AL CUIDADOR', 'codigo' => 'TSDII-CC-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 0, 'creditos' => 3],
                    ['nombre' => 'EMPRENDIMIENTO, ADMINISTRACIÓN Y GESTIÓN DE PROYECTOS', 'codigo' => 'TSDII-EAGP-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 0, 'creditos' => 3],
                    ['nombre' => 'RIESGOS Y EMERGENCIAS', 'codigo' => 'TSDII-RE-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 0, 'creditos' => 3],
                    ['nombre' => 'ATENCIÓN Y PROTECCIÓN A NIÑOS EN CONTEXTOS FAMILIARES E INSTITUCIONALES', 'codigo' => 'TSDII-APNCFI-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 0, 'creditos' => 3],
                    ['nombre' => 'DISEÑO Y ELABORACIÓN DE RECURSOS Y AMBIENTES DE APRENDIZAJE', 'codigo' => 'TSDII-DERAA-04', 'semestre' => 4, 'horas_semanales' => 3, 'horas_practicas' => 16, 'creditos' => 3],
                    ['nombre' => 'FORMULACIÓN DE PROYECTOS', 'codigo' => 'TSDII-FP-04', 'semestre' => 4, 'horas_semanales' => 5, 'horas_practicas' => 0, 'creditos' => 3]
                ]
            ]
        ];

        foreach ($asignaturas as $carreraAsignaturas) {
            $carrera = Carrera::where('codigo', $carreraAsignaturas['carrera_codigo'])->first();

            if ($carrera) {
                foreach ($carreraAsignaturas['asignaturas'] as $asignaturaData) {
                    $asignaturaData['carrera_id'] = $carrera->id;
                    $asignaturaData['descripcion'] = 'Asignatura de la carrera ' . $carrera->nombre;
                    $asignaturaData['activa'] = true;

                    Asignatura::create($asignaturaData);
                }

                $this->command->info("Asignaturas creadas para la carrera: {$carrera->nombre}");
            }
        }

        $this->command->info('Todas las asignaturas han sido creadas exitosamente.');
    }
}
