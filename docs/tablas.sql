CREATE TABLE tipo_usuario(
    id-tipo_usuario INT AUTO_INCREMENT PRIMARY KEY,
    rol VARCHAR(10) NOT NULL UNIQUE
);

INSERT INTO tipo_usuario(rol) VALUES
('Profesor'),
('Alumno'),
('Administrador');


CREATE TABLE cuenta(
    id_cuenta CHAR(36) PRIMARY KEY,
    correo VARCHAR(40) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_tipo_usuario INT NOT NULL,

    FOREIGN KEY (id_tipo_usuario) 
    REFERENCES tipo_usuario(id-tipo_usuario)
);

CREATE TABLE tipo_aprendizaje(
    id_tipo_aprendizaje INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(30) NOT NULL UNIQUE
);

INSERT INTO tipo_aprendizaje(tipo) VALUES
('Visual'),
('Auditivo'),
('Kinestésico'),
('Lectura/Escritura');

CREATE TABLE aprendizaje_cuenta(
    id_cuenta CHAR(36) NOT NULL,
    id_tipo_aprendizaje INT NOT NULL,

    FOREIGN KEY (id_cuenta)
    REFERENCES cuenta(id_cuenta),
    
    FOREIGN KEY (id_tipo_aprendizaje) 
    REFERENCES tipo_aprendizaje(id_tipo_aprendizaje)
);

CREATE TABLE turno(
    id_turno INT AUTO_INCREMENT PRIMARY KEY,
    turno VARCHAR(20) NOT NULL UNIQUE

);

INSERT INTO turno(turno) VALUES
('Matutino'),
('Vespertino');

CREATE TABLE ciclo_escolar(
    id_ciclo INT AUTO_INCREMENT PRIMARY KEY,
    periodo VARCHAR(20) NOT NULL
);

INSERT INTO ciclo_escolar(periodo) 
VALUES
('2024-2025'),
('2025-2026');

CREATE TABLE grupo(
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_grupo VARCHAR(20) NOT NULL,
    id_turno INT NOT NULL,
    id_ciclo INT NOT NULL,
    
    FOREIGN KEY (id_turno) 
    REFERENCES turno(id_turno),
    
    FOREIGN KEY (id_ciclo) 
    REFERENCES turno(id_ciclo)
);

INSERT INTO grupo(nombre_grupo, id_turno, id_ciclo) 
VALUES
('Grupo 61-A, FUNDACION'),
('Grupo 61-B, LACE-C'),
('Grupo 61-C, FUNDACION'),
('Grupo 61-D, LACE-C');

CREATE TABLE ciclo_cuenta(
    id_ciclo_cuenta INT AUTO_INCREMENT PRIMARY KEY,
    id_cuenta CHAR(36) NOT NULL,
    id_grupo INT NOT NULL,
    id_ciclo INT NOT NULL,

    FOREIGN KEY (id_cuenta)
    REFERENCES cuenta(id_cuenta),

    FOREIGN KEY (id_grupo)
    REFERENCES grupo(id_grupo),

    FOREIGN KEY (id_ciclo)
    REFERENCES ciclo_escolar(id_ciclo)
);


CREATE TABLE tipo_tarea(
    id_tipo_tarea INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE actividad(
    id_actividad CHAR(36) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(200),
    id_cuenta_profesor CHAR(36) NOT NULL,
    id_tipo_tarea INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_cuenta_profesor)
    REFERENCES cuenta(id_cuenta),

    FOREIGN KEY (id_tipo_tarea)
    REFERENCES tipo_tarea(id_tipo_tarea)
);

CREATE TABLE comentrario(
    id_comentario CHAR(36) PRIMARY KEY,
    contenido VARCHAR(500) NOT NULL,
    privado BOOL DEFAULT FALSE,
    id_cuenta CHAR(36) NOT NULL,
    id_actividad CHAR(36) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_cuenta)
    REFERENCES cuenta(id_cuenta),

    FOREIGN KEY (id_actividad)
    REFERENCES actividad(id_actividad)
);

CREATE TABLE actividad_grupo(
    id_actividad_grupo CHAR(36) PRIMARY KEY,
    id_actividad CHAR(36) NOT NULL,
    id_grupo INT NOT NULL,
    id_ciclo INT NOT NULL,
    fecha_de_entrega DATETIME NOT NULL,

    FOREIGN KEY (id_actividad)
    REFERENCES actividad(id_actividad),

    FOREIGN KEY (id_grupo)
    REFERENCES grupo(id_grupo),

    FOREIGN KEY (id_ciclo)
    REFERENCES ciclo_escolar(id_ciclo)
);

CREATE TABLE entrega(
    id_entrega CHAR(36) PRIMARY KEY,
    id_actividad_grupo CHAR(36) NOT NULL,
    id_cuenta CHAR(36) NOT NULL,
    entregado BOOL DEFAULT FALSE,
    calificacion DECIMAL(5,2),
    fecha_de_entrega DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_actividad_grupo)
    REFERENCES actividad_grupo(id_actividad_grupo),

    FOREIGN KEY (id_cuenta)
    REFERENCES cuenta(id_cuenta)
);

CREATE TABLE archivo(
    id_archivo CHAR(36) PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    ruta VARCHAR(300) NOT NULL,
    mime_type VARCHAR(50),
    id_entrega CHAR(36),
    id_actividad CHAR(36),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_entrega)
    REFERENCES entrega(id_entrega),

    FOREIGN KEY (id_actividad)
    REFERENCES actividad(id_actividad)
);

CREATE TABLE cuestionario(
    id_cuestionario CHAR(36) PRIMARY KEY,
    id_ciclo_cuenta INT NOT NULL,
    enlace VARCHAR(100) NOT NULL,

    FOREIGN KEY (id_ciclo_cuenta)
    REFERENCES ciclo_cuenta(id_ciclo_cuenta)
);

CREATE TABLE respuesta_cuestionario(
    id_respuesta_cuestionario CHAR(36) PRIMARY KEY,
    id_ciclo_cuenta INT NOT NULL,
    id_cuestionario CHAR(36) NOT NULL,
    respuesta VARCHAR(255) NOT NULL,

    FOREIGN KEY (id_ciclo_cuenta)
    REFERENCES ciclo_cuenta(id_ciclo_cuenta),

    FOREIGN KEY (id_cuestionario)
    REFERENCES cuestionario(id_cuestionario)
);

CREATE TABLE retroalimentacion_alumno(
    id_actividad_grupo CHAR(36) PRIMARY KEY,
    id_cuenta CHAR(36) NOT NULL PRIMARY KEY,
    valoracion INT NOT NULL,
    pregunta1 BOOL,
    pregunta2 BOOL,
    pregunta3 BOOL,
    pregunta4 BOOL:
);