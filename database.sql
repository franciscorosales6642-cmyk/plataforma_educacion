CREATE DATABASE IF NOT EXISTS plataforma_educacion CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE plataforma_educacion;

DROP TABLE IF EXISTS respuestas_preguntas;
DROP TABLE IF EXISTS preguntas;
DROP TABLE IF EXISTS reconocimientos;
DROP TABLE IF EXISTS calificaciones;
DROP TABLE IF EXISTS progreso;
DROP TABLE IF EXISTS videos;
DROP TABLE IF EXISTS cursos;
DROP TABLE IF EXISTS profesores;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    imagen VARCHAR(255) DEFAULT 'default.png',
    tema ENUM('claro','oscuro') DEFAULT 'claro',
    rol ENUM('ADMIN','USUARIO') DEFAULT 'USUARIO',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE profesores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    especialidad VARCHAR(100) NOT NULL
);

CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    profesor_id INT NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255) DEFAULT 'curso.png',
    FOREIGN KEY (profesor_id) REFERENCES profesores(id) ON DELETE CASCADE
);

CREATE TABLE videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    url_video VARCHAR(255) NOT NULL,
    orden INT NOT NULL,
    visualizaciones INT DEFAULT 0,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

CREATE TABLE progreso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    curso_id INT NOT NULL,
    video_id INT NOT NULL,
    visto BOOLEAN DEFAULT FALSE,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

CREATE TABLE preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    pregunta TEXT NOT NULL,
    opcion_a VARCHAR(255) NOT NULL,
    opcion_b VARCHAR(255) NOT NULL,
    opcion_c VARCHAR(255) NOT NULL,
    opcion_d VARCHAR(255) NOT NULL,
    respuesta_correcta ENUM('A','B','C','D') NOT NULL,
    orden INT NOT NULL,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

CREATE TABLE respuestas_preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pregunta_id INT NOT NULL,
    respuesta ENUM('A','B','C','D') NOT NULL,
    correcta BOOLEAN DEFAULT FALSE,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_respuesta_usuario (usuario_id, pregunta_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE CASCADE
);

CREATE TABLE calificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    curso_id INT NOT NULL,
    estrellas INT NOT NULL,
    comentario TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

CREATE TABLE reconocimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    curso_id INT NOT NULL,
    archivo VARCHAR(255) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

INSERT INTO usuarios(nombre, correo, password, rol) VALUES
('Administrador', 'admin@gmail.com', '12345', 'ADMIN'),
('Usuario Demo', 'usuario@gmail.com', '12345', 'USUARIO');

INSERT INTO profesores(nombre, correo, especialidad) VALUES
('Carlos Mendoza', 'carlos@escuela.com', 'Programacion Web'),
('Ana Lopez', 'ana@escuela.com', 'Diseno UX/UI');

INSERT INTO cursos(nombre, profesor_id, descripcion) VALUES
('Curso basico de PHP', 1, 'Aprende PHP desde cero usando XAMPP y MySQL.'),
('Diseno de interfaces moviles', 2, 'Aprende fundamentos de diseno para aplicaciones Android.'),
('Fundamentos de Programacion', 1, 'Comprende variables, condicionales, ciclos y logica para empezar a programar.'),
('Programacion en Java desde Cero', 1, 'Aprende sintaxis, clases, objetos y estructuras basicas con Java.'),
('Desarrollo Web con HTML, CSS y JavaScript', 1, 'Construye interfaces web modernas y dinamicas desde cero.'),
('Python para Principiantes', 1, 'Domina los conceptos esenciales de Python con ejercicios practicos.'),
('Estructuras de Datos en Java', 1, 'Trabaja con listas, pilas, colas y mapas para resolver problemas reales.'),
('JavaScript Avanzado', 1, 'Profundiza en funciones, asincronia, consumo de APIs y manipulacion del DOM.'),
('Desarrollo Backend con PHP y MySQL', 1, 'Crea sistemas backend conectados a base de datos usando PHP.'),
('Introduccion a C# y .NET', 1, 'Explora la programacion orientada a objetos con C# y el ecosistema .NET.'),
('Programacion de Aplicaciones Moviles', 1, 'Aprende la base de apps moviles y su integracion con servicios.'),
('Algoritmos y Resolucion de Problemas', 1, 'Mejora tu pensamiento logico con algoritmos clasicos y practicas guiadas.');

INSERT INTO videos(curso_id, titulo, url_video, orden) VALUES
(1, 'Introduccion a PHP', 'videos/php1.mp4', 1),
(1, 'Conexion a MySQL', 'videos/php2.mp4', 2),
(2, 'Introduccion al diseno movil', 'videos/diseno1.mp4', 1),
(2, 'Colores y temas', 'videos/diseno2.mp4', 2),
(3, 'Variables, tipos de datos y operadores', 'videos/fundamentos_programacion_1.mp4', 1),
(3, 'Condicionales, ciclos y logica', 'videos/fundamentos_programacion_2.mp4', 2),
(4, 'Sintaxis basica y estructura de Java', 'videos/java_cero_1.mp4', 1),
(4, 'Clases, objetos y metodos', 'videos/java_cero_2.mp4', 2),
(5, 'Estructura HTML y estilos con CSS', 'videos/desarrollo_web_1.mp4', 1),
(5, 'Interactividad con JavaScript', 'videos/desarrollo_web_2.mp4', 2),
(6, 'Introduccion a Python y variables', 'videos/python_principiantes_1.mp4', 1),
(6, 'Funciones, listas y condicionales', 'videos/python_principiantes_2.mp4', 2),
(7, 'Listas, pilas y colas en Java', 'videos/estructuras_java_1.mp4', 1),
(7, 'Mapas y colecciones clave-valor', 'videos/estructuras_java_2.mp4', 2),
(8, 'Funciones avanzadas y scope', 'videos/javascript_avanzado_1.mp4', 1),
(8, 'Promesas, async y consumo de APIs', 'videos/javascript_avanzado_2.mp4', 2),
(9, 'Rutas, formularios y backend en PHP', 'videos/backend_php_mysql_1.mp4', 1),
(9, 'Consultas SQL y persistencia de datos', 'videos/backend_php_mysql_2.mp4', 2),
(10, 'Sintaxis de C# y tipos de datos', 'videos/csharp_dotnet_1.mp4', 1),
(10, 'POO y estructura de proyectos .NET', 'videos/csharp_dotnet_2.mp4', 2),
(11, 'Fundamentos de interfaces moviles', 'videos/apps_moviles_1.mp4', 1),
(11, 'Navegacion y consumo de servicios', 'videos/apps_moviles_2.mp4', 2),
(12, 'Introduccion a algoritmos y diagramas', 'videos/algoritmos_1.mp4', 1),
(12, 'Resolucion de problemas paso a paso', 'videos/algoritmos_2.mp4', 2);

INSERT INTO preguntas(curso_id, pregunta, opcion_a, opcion_b, opcion_c, opcion_d, respuesta_correcta, orden) VALUES
(3, 'Que estructura se usa para repetir instrucciones varias veces?', 'for', 'class', 'import', 'return', 'A', 1),
(3, 'Que palabra se usa para tomar una decision en programacion?', 'while', 'switch', 'if', 'print', 'C', 2),
(3, 'Que representa una variable?', 'Un comentario', 'Un espacio para guardar datos', 'Una libreria', 'Una imagen', 'B', 3),
(3, 'Que tipo de error ocurre cuando la logica del programa esta mal pero compila?', 'Error visual', 'Error logico', 'Error de red', 'Error de sintaxis HTML', 'B', 4),
(3, 'Que operador suele usarse para comparar igualdad?', '=', '==', '+=', '=>', 'B', 5),

(4, 'Que palabra clave define una clase en Java?', 'function', 'class', 'new', 'import', 'B', 1),
(4, 'Que metodo es el punto de entrada de un programa Java?', 'run()', 'start()', 'main()', 'init()', 'C', 2),
(4, 'Que tipo se usa para texto en Java?', 'char[]', 'String', 'text', 'varchar', 'B', 3),
(4, 'Que palabra crea un nuevo objeto?', 'make', 'build', 'object', 'new', 'D', 4),
(4, 'Java es principalmente un lenguaje...', 'Interpretado de base de datos', 'Orientado a objetos', 'Solo de marcado', 'Solo funcional', 'B', 5),

(5, 'Que etiqueta define el contenido principal de una pagina HTML?', 'body', 'head', 'script', 'style', 'A', 1),
(5, 'Que propiedad CSS cambia el color del texto?', 'background', 'font-size', 'color', 'display', 'C', 2),
(5, 'Que lenguaje agrega interactividad en el navegador?', 'SQL', 'JavaScript', 'CSS', 'XML', 'B', 3),
(5, 'Que selector CSS apunta a un elemento por id?', '.clase', '#id', '*', 'tag', 'B', 4),
(5, 'Que metodo muestra un mensaje en consola en JavaScript?', 'echo()', 'print()', 'console.log()', 'prompt.log()', 'C', 5),

(6, 'Que simbolo se usa para comentarios de una linea en Python?', '//', '#', '--', '/*', 'B', 1),
(6, 'Como se define una funcion en Python?', 'function nombre()', 'def nombre():', 'fn nombre()', 'new function()', 'B', 2),
(6, 'Que tipo de dato guarda verdadero o falso?', 'bool', 'string', 'float', 'list', 'A', 3),
(6, 'Que estructura almacena varios elementos ordenados?', 'list', 'if', 'import', 'class', 'A', 4),
(6, 'Que funcion imprime en pantalla en Python?', 'echo()', 'print()', 'write()', 'output()', 'B', 5),

(7, 'Que estructura funciona con el principio LIFO?', 'Cola', 'Pila', 'Lista doble', 'Arreglo fijo', 'B', 1),
(7, 'Que estructura funciona con el principio FIFO?', 'Pila', 'Cola', 'Mapa', 'Arbol binario', 'B', 2),
(7, 'Que estructura almacena pares clave-valor en Java?', 'Stack', 'Queue', 'Map', 'Scanner', 'C', 3),
(7, 'Que operacion agrega un elemento al final de una lista?', 'push()', 'add()', 'read()', 'close()', 'B', 4),
(7, 'Que estructura es mejor para busquedas jerarquicas?', 'Arbol', 'String', 'Boolean', 'Switch', 'A', 5),

(8, 'Que concepto permite ejecutar codigo despues de una operacion asincrona?', 'Promise', 'Padding', 'Selector', 'Template HTML', 'A', 1),
(8, 'Que metodo convierte un objeto a JSON?', 'JSON.parse()', 'JSON.stringify()', 'toString()', 'Object.json()', 'B', 2),
(8, 'Que evento se dispara al hacer clic?', 'hover', 'load', 'click', 'changeColor', 'C', 3),
(8, 'Que palabra se usa para esperar una promesa dentro de una funcion async?', 'wait', 'await', 'hold', 'pause', 'B', 4),
(8, 'Que metodo selecciona el primer elemento que coincide con un selector CSS?', 'getOne()', 'querySelector()', 'findFirst()', 'element()', 'B', 5),

(9, 'Que lenguaje se usa para consultar MySQL en este curso?', 'CSS', 'SQL', 'XML', 'Markdown', 'B', 1),
(9, 'Que extension suelen tener los archivos PHP?', '.js', '.php', '.sql', '.html5', 'B', 2),
(9, 'Que sentencia SQL inserta registros?', 'SELECT', 'DELETE', 'INSERT', 'DROP', 'C', 3),
(9, 'Que funcion de MySQLi ayuda a escapar texto en PHP?', 'safe_text()', 'real_escape_string()', 'secure_query()', 'escape_html()', 'B', 4),
(9, 'Que se necesita para conectar PHP con MySQL?', 'Una hoja CSS', 'Una conexion a base de datos', 'Un video MP4', 'Un archivo APK', 'B', 5),

(10, 'Que plataforma usa C# para muchas aplicaciones empresariales?', 'Node', '.NET', 'Laravel', 'Django', 'B', 1),
(10, 'Que palabra define una clase en C#?', 'class', 'struct', 'module', 'package', 'A', 2),
(10, 'Que metodo suele ser punto de entrada en C#?', 'Main', 'Start', 'Run', 'Boot', 'A', 3),
(10, 'Que simbolo termina instrucciones en C# comunmente?', ';', ':', '#', '@', 'A', 4),
(10, 'C# es un lenguaje fuertemente...', 'Tipado', 'Comentado', 'Visual', 'Secuencial web', 'A', 5),

(11, 'Que enfoque es comun al crear apps moviles modernas?', 'Interfaces responsivas', 'Solo SQL plano', 'Solo archivos ZIP', 'Solo consola', 'A', 1),
(11, 'Que componente muestra listas de contenido en muchas apps?', 'Recycler o lista', 'Firewall', 'Compresor ZIP', 'Cronometro', 'A', 2),
(11, 'Que permiso suele requerirse para usar la camara?', 'Acceso a SMS obligatorio', 'Permiso de camara', 'Acceso al kernel', 'FTP remoto', 'B', 3),
(11, 'Que practica mejora la experiencia movil?', 'Botones pequenos', 'Navegacion clara', 'Pantallas sin jerarquia', 'Texto invisible', 'B', 4),
(11, 'Que servicio permite consumir datos remotos en apps?', 'API', 'BIOS', 'Cache del monitor', 'Teclado numerico', 'A', 5),

(12, 'Que describe mejor un algoritmo?', 'Una imagen decorativa', 'Una serie de pasos para resolver un problema', 'Un tema visual', 'Un tipo de fuente', 'B', 1),
(12, 'Que tecnica divide un problema en partes mas pequenas?', 'Descomposicion', 'Hover', 'Padding', 'Streaming', 'A', 2),
(12, 'Que herramienta ayuda a representar decisiones en pasos?', 'Diagrama de flujo', 'GIF', 'Tabla de estilos', 'Archivo MP3', 'A', 3),
(12, 'Que se busca al optimizar un algoritmo?', 'Mas consumo de recursos', 'Mejor rendimiento', 'Mas colores', 'Mas ventanas abiertas', 'B', 4),
(12, 'Que estructura condicional evalua diferentes casos?', 'switch', 'image', 'font', 'export', 'A', 5);
