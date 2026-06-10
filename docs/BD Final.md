```yaml-header
```

# CRUD y Schema

---

## Schema de Base de Datos

```sql
-- CATÁLOGOS

CREATE TABLE tipo_usuario (
    id_tipo_usuario INT         AUTO_INCREMENT PRIMARY KEY,
    rol             VARCHAR(10) NOT NULL UNIQUE
);

INSERT INTO tipo_usuario (rol) VALUES
    ('Alumno'),
    ('Profesor'),
    ('Administrador');

CREATE TABLE tipo_aprendizaje (
    id_tipo_aprendizaje INT         AUTO_INCREMENT PRIMARY KEY,
    tipo                VARCHAR(30) NOT NULL UNIQUE
);

INSERT INTO tipo_aprendizaje (tipo) VALUES
    ('Visual'),
    ('Auditivo'),
    ('Kinestésico'),
    ('Lectura/Escritura');

CREATE TABLE turno (
    id_turno INT         AUTO_INCREMENT PRIMARY KEY,
    turno    VARCHAR(20) NOT NULL UNIQUE
);

INSERT INTO turno (turno) VALUES
    ('Matutino'),
    ('Vespertino');

CREATE TABLE tipo_tarea (
    id_tipo_tarea INT          AUTO_INCREMENT PRIMARY KEY,
    tipo          VARCHAR(100) NOT NULL UNIQUE
);

INSERT INTO tipo_tarea (tipo) VALUES
    ('Actividad'),
    ('Tarea');

-- CUENTA

CREATE TABLE cuenta (
    id_cuenta       CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    correo          VARCHAR(40)  NOT NULL UNIQUE,
    nombre          VARCHAR(50)  NOT NULL,
    contraseña      VARCHAR(255) NOT NULL,
    id_tipo_usuario INT          NOT NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tipo_usuario) REFERENCES tipo_usuario (id_tipo_usuario)
);

CREATE TABLE aprendizaje_cuenta (
    id_cuenta           CHAR(36) NOT NULL,
    id_tipo_aprendizaje INT      NOT NULL,
    PRIMARY KEY (id_cuenta, id_tipo_aprendizaje),
    FOREIGN KEY (id_cuenta)           REFERENCES cuenta           (id_cuenta),
    FOREIGN KEY (id_tipo_aprendizaje) REFERENCES tipo_aprendizaje (id_tipo_aprendizaje)
);

-- CICLO ESCOLAR Y GRUPOS

CREATE TABLE ciclo_escolar (
    id_ciclo INT         AUTO_INCREMENT PRIMARY KEY,
    periodo  VARCHAR(20) NOT NULL UNIQUE
);

INSERT INTO ciclo_escolar (periodo) VALUES
    ('2024-2025'),
    ('2025-2026'),
    ('2026-2027'),
    ('2027-2028');

CREATE TABLE grupo (
    id_grupo     INT         AUTO_INCREMENT PRIMARY KEY,
    nombre_grupo VARCHAR(20) NOT NULL,
    id_turno     INT         NOT NULL,
    id_ciclo     INT         NOT NULL,
    FOREIGN KEY (id_turno) REFERENCES turno         (id_turno),
    FOREIGN KEY (id_ciclo) REFERENCES ciclo_escolar (id_ciclo)
);

INSERT INTO grupo (nombre_grupo, id_turno, id_ciclo) VALUES
    ('61-A', 1, 4),
    ('61-B', 1, 4),
    ('61-C', 2, 4),
    ('61-D', 2, 4),
    ('62-A', 1, 4),
    ('62-B', 2, 4),
    ('62-C', 2, 4);

CREATE TABLE ciclo_cuenta (
    id_ciclo_cuenta CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    id_cuenta       CHAR(36) NOT NULL,
    id_grupo        INT      NOT NULL,
    id_ciclo        INT      NOT NULL,
    UNIQUE (id_cuenta, id_ciclo),
    FOREIGN KEY (id_cuenta) REFERENCES cuenta        (id_cuenta),
    FOREIGN KEY (id_grupo)  REFERENCES grupo         (id_grupo),
    FOREIGN KEY (id_ciclo)  REFERENCES ciclo_escolar (id_ciclo)
);

-- ACTIVIDADES

CREATE TABLE actividad (
    id_actividad       CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    nombre             VARCHAR(100) NOT NULL,
    descripcion        VARCHAR(200),
    id_cuenta_profesor CHAR(36)     NOT NULL,
    id_tipo_tarea      INT          NOT NULL,
    created_at         DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cuenta_profesor) REFERENCES cuenta     (id_cuenta),
    FOREIGN KEY (id_tipo_tarea)      REFERENCES tipo_tarea (id_tipo_tarea)
);

CREATE TABLE actividad_grupo (
    id_actividad_grupo CHAR(36) PRIMARY KEY DEFAULT (UUID()),
    id_actividad       CHAR(36) NOT NULL,
    id_grupo           INT      NOT NULL,
    id_ciclo           INT      NOT NULL,
    fecha_de_entrega   DATETIME NOT NULL,
    UNIQUE (id_actividad, id_grupo, id_ciclo),
    FOREIGN KEY (id_actividad) REFERENCES actividad     (id_actividad),
    FOREIGN KEY (id_grupo)     REFERENCES grupo         (id_grupo),
    FOREIGN KEY (id_ciclo)     REFERENCES ciclo_escolar (id_ciclo)
);

-- COMENTARIOS

CREATE TABLE comentario (
    id_comentario CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    contenido     VARCHAR(500) NOT NULL,
    privado       BOOL         DEFAULT FALSE,
    id_cuenta     CHAR(36)     NOT NULL,
    id_actividad  CHAR(36)     NOT NULL,
    created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cuenta)    REFERENCES cuenta    (id_cuenta),
    FOREIGN KEY (id_actividad) REFERENCES actividad (id_actividad)
);

-- ENTREGAS Y ARCHIVOS

CREATE TABLE entrega (
    id_entrega         CHAR(36)         PRIMARY KEY DEFAULT (UUID()),
    id_actividad_grupo CHAR(36)         NOT NULL,
    id_cuenta          CHAR(36)         NOT NULL,
    entregado          BOOL             DEFAULT FALSE,
    calificacion       TINYINT UNSIGNED CHECK (calificacion <= 100),
    fecha_de_entrega   DATETIME,
    created_at         DATETIME         DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_actividad_grupo, id_cuenta),
    FOREIGN KEY (id_actividad_grupo) REFERENCES actividad_grupo (id_actividad_grupo),
    FOREIGN KEY (id_cuenta)          REFERENCES cuenta          (id_cuenta)
);

CREATE TABLE archivo (
    id_archivo   CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    nombre       VARCHAR(50)  NOT NULL,
    ruta         VARCHAR(300) NOT NULL,
    mime_type    VARCHAR(100),
    id_entrega   CHAR(36),
    id_actividad CHAR(36),
    created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_entrega)   REFERENCES entrega   (id_entrega),
    FOREIGN KEY (id_actividad) REFERENCES actividad (id_actividad)
);

-- CUESTIONARIO Y RETROALIMENTACIÓN

CREATE TABLE cuestionario (
    id_cuestionario CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    enlace          VARCHAR(100) NOT NULL,
    id_ciclo_cuenta CHAR(36)     NOT NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ciclo_cuenta) REFERENCES ciclo_cuenta (id_ciclo_cuenta)
);

CREATE TABLE retroalimentacion_cuestionario (
    id_retroalimentacion CHAR(36)     PRIMARY KEY DEFAULT (UUID()),
    id_cuestionario      CHAR(36)     NOT NULL,
    id_ciclo_cuenta      CHAR(36)     NOT NULL,
    respuesta            VARCHAR(255) NOT NULL,
    created_at           DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cuestionario) REFERENCES cuestionario  (id_cuestionario),
    FOREIGN KEY (id_ciclo_cuenta) REFERENCES ciclo_cuenta  (id_ciclo_cuenta)
);

CREATE TABLE retroalimentacion_alumno (
    id_actividad_grupo CHAR(36)         NOT NULL,
    id_cuenta          CHAR(36)         NOT NULL,
    valoracion         TINYINT UNSIGNED CHECK (valoracion <= 5),
    pregunta1          BOOL,
    pregunta2          BOOL,
    pregunta3          BOOL,
    pregunta4          BOOL,
    PRIMARY KEY (id_actividad_grupo, id_cuenta),
    FOREIGN KEY (id_actividad_grupo) REFERENCES actividad_grupo (id_actividad_grupo),
    FOREIGN KEY (id_cuenta)          REFERENCES cuenta          (id_cuenta)
);
```

---

## CRUD por Entidad

---

### Entidad: tipo_usuario

#### CREATE
```sql
INSERT INTO tipo_usuario (rol)
VALUES ('Docente');
```

#### READ
Todos:
```sql
SELECT id_tipo_usuario, rol
FROM tipo_usuario;
```
Uno:
```sql
SELECT id_tipo_usuario, rol
FROM tipo_usuario
WHERE id_tipo_usuario = 1;
```

#### UPDATE
```sql
UPDATE tipo_usuario
SET rol = 'Docente'
WHERE id_tipo_usuario = 2;
```

#### DELETE
```sql
DELETE FROM tipo_usuario
WHERE id_tipo_usuario = 2;
```

---

### Entidad: tipo_aprendizaje

#### CREATE
```sql
INSERT INTO tipo_aprendizaje (tipo)
VALUES ('Visual');
```

#### READ
Todos:
```sql
SELECT id_tipo_aprendizaje, tipo
FROM tipo_aprendizaje;
```
Uno:
```sql
SELECT id_tipo_aprendizaje, tipo
FROM tipo_aprendizaje
WHERE id_tipo_aprendizaje = 2;
```

#### UPDATE
```sql
UPDATE tipo_aprendizaje
SET tipo = 'Lectura / Escritura'
WHERE id_tipo_aprendizaje = 4;
```

#### DELETE
```sql
DELETE FROM tipo_aprendizaje
WHERE id_tipo_aprendizaje = 4;
```

---

### Entidad: turno

#### CREATE
```sql
INSERT INTO turno (turno)
VALUES ('Nocturno');
```

#### READ
Todos:
```sql
SELECT id_turno, turno
FROM turno;
```
Uno:
```sql
SELECT id_turno, turno
FROM turno
WHERE id_turno = 1;
```

#### UPDATE
```sql
UPDATE turno
SET turno = 'Matutino'
WHERE id_turno = 1;
```

#### DELETE
```sql
DELETE FROM turno
WHERE id_turno = 1;
```

---

### Entidad: tipo_tarea

#### CREATE
```sql
INSERT INTO tipo_tarea (tipo)
VALUES ('Proyecto');
```

#### READ
Todos:
```sql
SELECT id_tipo_tarea, tipo
FROM tipo_tarea;
```
Uno:
```sql
SELECT id_tipo_tarea, tipo
FROM tipo_tarea
WHERE id_tipo_tarea = 1;
```

#### UPDATE
```sql
UPDATE tipo_tarea
SET tipo = 'Actividad en Clase'
WHERE id_tipo_tarea = 1;
```

#### DELETE
```sql
DELETE FROM tipo_tarea
WHERE id_tipo_tarea = 1;
```

---

### Entidad: ciclo_escolar

#### CREATE
```sql
INSERT INTO ciclo_escolar (periodo)
VALUES ('2028-2029');
```

#### READ
Todos:
```sql
SELECT id_ciclo, periodo
FROM ciclo_escolar;
```
Uno:
```sql
SELECT id_ciclo, periodo
FROM ciclo_escolar
WHERE id_ciclo = 4;
```

#### UPDATE
```sql
UPDATE ciclo_escolar
SET periodo = '2024-2025'
WHERE id_ciclo = 1;
```

#### DELETE
```sql
DELETE FROM ciclo_escolar
WHERE id_ciclo = 1;
```

---

### Entidad: cuenta

#### CREATE
```sql
INSERT INTO cuenta (id_cuenta, correo, nombre, contraseña, id_tipo_usuario)
VALUES (UUID(), 'juan.perez@escuela.edu.mx', 'Juan Pérez', '$2y$10$hash_bcrypt', 2);
```

#### READ
Todos (sin aliases):
```sql
SELECT cuenta.id_cuenta, cuenta.correo, cuenta.nombre, tipo_usuario.rol, cuenta.created_at
FROM cuenta
INNER JOIN tipo_usuario ON cuenta.id_tipo_usuario = tipo_usuario.id_tipo_usuario;
```
Todos (con aliases):
```sql
SELECT c.id_cuenta, c.correo, c.nombre, t.rol, c.created_at
FROM cuenta c
INNER JOIN tipo_usuario t ON c.id_tipo_usuario = t.id_tipo_usuario;
```
Un tipo (sin aliases):
```sql
SELECT cuenta.id_cuenta, cuenta.correo, cuenta.nombre, cuenta.created_at
FROM cuenta
INNER JOIN tipo_usuario ON cuenta.id_tipo_usuario = tipo_usuario.id_tipo_usuario
WHERE tipo_usuario.rol = 'Profesor';
```
Un tipo (con aliases):
```sql
SELECT c.id_cuenta, c.correo, c.nombre, c.created_at
FROM cuenta c
INNER JOIN tipo_usuario t ON c.id_tipo_usuario = t.id_tipo_usuario
WHERE t.rol = 'Profesor';
```
Uno (sin aliases):
```sql
SELECT cuenta.id_cuenta, cuenta.correo, cuenta.nombre, tipo_usuario.rol, cuenta.created_at
FROM cuenta
INNER JOIN tipo_usuario ON cuenta.id_tipo_usuario = tipo_usuario.id_tipo_usuario
WHERE cuenta.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```
Uno (con aliases):
```sql
SELECT c.id_cuenta, c.correo, c.nombre, t.rol, c.created_at
FROM cuenta c
INNER JOIN tipo_usuario t ON c.id_tipo_usuario = t.id_tipo_usuario
WHERE c.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

#### UPDATE
```sql
UPDATE cuenta
SET correo = 'juan.nuevo@escuela.edu.mx', nombre = 'Juan Pérez García'
WHERE id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

#### DELETE
```sql
DELETE FROM cuenta
WHERE id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

---

### Entidad: aprendizaje_cuenta

#### CREATE
```sql
INSERT INTO aprendizaje_cuenta (id_cuenta, id_tipo_aprendizaje)
VALUES ('a1b2c3d4-e5f6-7890-abcd-ef1234567890', 2);
```

#### READ
Todos (incluye alumnos sin estilo registrado):
```sql
SELECT c.nombre, ta.tipo AS tipo_aprendizaje
FROM cuenta c
LEFT JOIN aprendizaje_cuenta ac ON c.id_cuenta = ac.id_cuenta
LEFT JOIN tipo_aprendizaje   ta ON ac.id_tipo_aprendizaje = ta.id_tipo_aprendizaje;
-- LEFT JOIN: aparecen alumnos sin estilo con NULL
```
Por tipo de aprendizaje (solo los que tienen estilo):
```sql
SELECT c.nombre, ta.tipo AS tipo_aprendizaje
FROM aprendizaje_cuenta ac
INNER JOIN cuenta           c  ON ac.id_cuenta = c.id_cuenta
INNER JOIN tipo_aprendizaje ta ON ac.id_tipo_aprendizaje = ta.id_tipo_aprendizaje
WHERE ta.tipo = 'Visual';
-- INNER JOIN: si existe el registro, ambos lados existen obligatoriamente
```
Uno (estilo de un alumno específico):
```sql
SELECT c.nombre, ta.tipo AS tipo_aprendizaje
FROM aprendizaje_cuenta ac
INNER JOIN cuenta           c  ON ac.id_cuenta = c.id_cuenta
INNER JOIN tipo_aprendizaje ta ON ac.id_tipo_aprendizaje = ta.id_tipo_aprendizaje
WHERE ac.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```
Alumnos sin estilo registrado:
```sql
SELECT c.nombre
FROM cuenta c
LEFT JOIN aprendizaje_cuenta ac ON c.id_cuenta = ac.id_cuenta
WHERE ac.id_cuenta IS NULL;
-- LEFT JOIN EXCLUDING: solo los que no tienen estilo
```

#### UPDATE
```sql
UPDATE aprendizaje_cuenta
SET id_tipo_aprendizaje = 3
WHERE id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

#### DELETE
```sql
DELETE FROM aprendizaje_cuenta
WHERE id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

---

### Entidad: grupo

#### CREATE
```sql
INSERT INTO grupo (nombre_grupo, id_turno, id_ciclo)
VALUES ('63-A', 1, 4);
```

#### READ
Todos (con turno y ciclo):
```sql
SELECT g.id_grupo, g.nombre_grupo, t.turno, ce.periodo
FROM grupo g
INNER JOIN turno         t  ON g.id_turno = t.id_turno
INNER JOIN ciclo_escolar ce ON g.id_ciclo = ce.id_ciclo;
-- INNER JOIN: todo grupo DEBE tener turno y ciclo
```
Por turno:
```sql
SELECT g.id_grupo, g.nombre_grupo, t.turno, ce.periodo
FROM grupo g
INNER JOIN turno         t  ON g.id_turno = t.id_turno
INNER JOIN ciclo_escolar ce ON g.id_ciclo = ce.id_ciclo
WHERE t.turno = 'Matutino';
```
Por ciclo:
```sql
SELECT g.id_grupo, g.nombre_grupo, t.turno
FROM grupo g
INNER JOIN turno t ON g.id_turno = t.id_turno
WHERE g.id_ciclo = 4;
```
Uno:
```sql
SELECT g.id_grupo, g.nombre_grupo, t.turno, ce.periodo
FROM grupo g
INNER JOIN turno         t  ON g.id_turno = t.id_turno
INNER JOIN ciclo_escolar ce ON g.id_ciclo = ce.id_ciclo
WHERE g.id_grupo = 1;
```

#### UPDATE
```sql
UPDATE grupo
SET nombre_grupo = '63-B', id_turno = 2
WHERE id_grupo = 5;
```

#### DELETE
```sql
DELETE FROM grupo
WHERE id_grupo = 5;
```

---

### Entidad: ciclo_cuenta

#### CREATE
```sql
INSERT INTO ciclo_cuenta (id_ciclo_cuenta, id_cuenta, id_grupo, id_ciclo)
VALUES (UUID(), 'a1b2c3d4-e5f6-7890-abcd-ef1234567890', 1, 4);
```

#### READ
Todos:
```sql
SELECT cc.id_ciclo_cuenta, c.nombre, t.rol, g.nombre_grupo, ce.periodo
FROM ciclo_cuenta cc
INNER JOIN cuenta        c  ON cc.id_cuenta = c.id_cuenta
INNER JOIN tipo_usuario  t  ON c.id_tipo_usuario = t.id_tipo_usuario
INNER JOIN grupo         g  ON cc.id_grupo = g.id_grupo
INNER JOIN ciclo_escolar ce ON cc.id_ciclo = ce.id_ciclo;
-- INNER JOIN: todas las FK son obligatorias
```
Por tipo de usuario (rol):
```sql
SELECT cc.id_ciclo_cuenta, c.nombre, g.nombre_grupo, ce.periodo
FROM ciclo_cuenta cc
INNER JOIN cuenta        c  ON cc.id_cuenta = c.id_cuenta
INNER JOIN tipo_usuario  t  ON c.id_tipo_usuario = t.id_tipo_usuario
INNER JOIN grupo         g  ON cc.id_grupo = g.id_grupo
INNER JOIN ciclo_escolar ce ON cc.id_ciclo = ce.id_ciclo
WHERE t.rol = 'Alumno';
```
Uno:
```sql
SELECT cc.id_ciclo_cuenta, c.nombre, g.nombre_grupo, ce.periodo
FROM ciclo_cuenta cc
INNER JOIN cuenta        c  ON cc.id_cuenta = c.id_cuenta
INNER JOIN grupo         g  ON cc.id_grupo = g.id_grupo
INNER JOIN ciclo_escolar ce ON cc.id_ciclo = ce.id_ciclo
WHERE cc.id_ciclo_cuenta = 'b2c3d4e5-f6a7-8901-bcde-f12345678901';
```
Por grupo:
```sql
SELECT c.nombre, t.rol, g.nombre_grupo, ce.periodo
FROM ciclo_cuenta cc
INNER JOIN cuenta        c  ON cc.id_cuenta = c.id_cuenta
INNER JOIN tipo_usuario  t  ON c.id_tipo_usuario = t.id_tipo_usuario
INNER JOIN grupo         g  ON cc.id_grupo = g.id_grupo
INNER JOIN ciclo_escolar ce ON cc.id_ciclo = ce.id_ciclo
WHERE cc.id_grupo = 1;
```
Historial de un alumno (todos sus ciclos):
```sql
SELECT g.nombre_grupo, ce.periodo, t.turno
FROM ciclo_cuenta cc
INNER JOIN grupo         g  ON cc.id_grupo = g.id_grupo
INNER JOIN ciclo_escolar ce ON cc.id_ciclo = ce.id_ciclo
INNER JOIN turno         t  ON g.id_turno = t.id_turno
WHERE cc.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```
Alumnos sin grupo en un ciclo:
```sql
SELECT c.nombre
FROM cuenta c
LEFT JOIN ciclo_cuenta cc ON c.id_cuenta = cc.id_cuenta
                          AND cc.id_ciclo = 4
WHERE cc.id_ciclo_cuenta IS NULL;
-- LEFT JOIN EXCLUDING: alumnos no inscritos en ese ciclo
```

#### UPDATE
```sql
UPDATE ciclo_cuenta
SET id_grupo = 2
WHERE id_ciclo_cuenta = 'b2c3d4e5-f6a7-8901-bcde-f12345678901';
```

#### DELETE
```sql
DELETE FROM ciclo_cuenta
WHERE id_ciclo_cuenta = 'b2c3d4e5-f6a7-8901-bcde-f12345678901';
```

---

### Entidad: actividad

#### CREATE
```sql
INSERT INTO actividad (id_actividad, nombre, descripcion, id_cuenta_profesor, id_tipo_tarea)
VALUES (UUID(), 'Diagrama entidad-relación',
        'Diseñar el ER de una base de datos escolar',
        'a1b2c3d4-e5f6-7890-abcd-ef1234567890', 1);
```

#### READ
Todas:
```sql
SELECT a.id_actividad, a.nombre, c.nombre AS profesor, tt.tipo, a.created_at
FROM actividad a
INNER JOIN cuenta     c  ON a.id_cuenta_profesor = c.id_cuenta
INNER JOIN tipo_tarea tt ON a.id_tipo_tarea = tt.id_tipo_tarea;
-- INNER JOIN: toda actividad DEBE tener profesor y tipo
```
Por tipo de tarea:
```sql
SELECT a.id_actividad, a.nombre, c.nombre AS profesor, a.created_at
FROM actividad a
INNER JOIN cuenta     c  ON a.id_cuenta_profesor = c.id_cuenta
INNER JOIN tipo_tarea tt ON a.id_tipo_tarea = tt.id_tipo_tarea
WHERE tt.tipo = 'Tarea';
```
Por profesor:
```sql
SELECT a.id_actividad, a.nombre, tt.tipo, a.created_at
FROM actividad a
INNER JOIN tipo_tarea tt ON a.id_tipo_tarea = tt.id_tipo_tarea
WHERE a.id_cuenta_profesor = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```
Una:
```sql
SELECT a.id_actividad, a.nombre, a.descripcion, c.nombre AS profesor, tt.tipo
FROM actividad a
INNER JOIN cuenta     c  ON a.id_cuenta_profesor = c.id_cuenta
INNER JOIN tipo_tarea tt ON a.id_tipo_tarea = tt.id_tipo_tarea
WHERE a.id_actividad = 'c3d4e5f6-a7b8-9012-cdef-123456789012';
```

#### UPDATE
```sql
UPDATE actividad
SET nombre = 'Diagrama ER normalizado', descripcion = 'Aplicar 3FN al diseño'
WHERE id_actividad = 'c3d4e5f6-a7b8-9012-cdef-123456789012';
```

#### DELETE
```sql
DELETE FROM actividad
WHERE id_actividad = 'c3d4e5f6-a7b8-9012-cdef-123456789012';
```

---

### Entidad: actividad_grupo

#### CREATE
```sql
INSERT INTO actividad_grupo (id_actividad_grupo, id_actividad, id_grupo, id_ciclo, fecha_de_entrega)
VALUES (UUID(), 'c3d4e5f6-a7b8-9012-cdef-123456789012', 1, 4, '2025-03-15 23:59:00');
```

#### READ
Todas:
```sql
SELECT ag.id_actividad_grupo, a.nombre, g.nombre_grupo, ce.periodo, ag.fecha_de_entrega
FROM actividad_grupo ag
INNER JOIN actividad     a  ON ag.id_actividad = a.id_actividad
INNER JOIN grupo         g  ON ag.id_grupo = g.id_grupo
INNER JOIN ciclo_escolar ce ON ag.id_ciclo = ce.id_ciclo;
-- INNER JOIN: todas las relaciones son obligatorias
```
Por tipo de tarea:
```sql
SELECT ag.id_actividad_grupo, a.nombre, g.nombre_grupo, ag.fecha_de_entrega
FROM actividad_grupo ag
INNER JOIN actividad  a  ON ag.id_actividad = a.id_actividad
INNER JOIN tipo_tarea tt ON a.id_tipo_tarea = tt.id_tipo_tarea
INNER JOIN grupo      g  ON ag.id_grupo = g.id_grupo
WHERE tt.tipo = 'Tarea';
```
Uno:
```sql
SELECT ag.id_actividad_grupo, a.nombre, g.nombre_grupo, ce.periodo, ag.fecha_de_entrega
FROM actividad_grupo ag
INNER JOIN actividad     a  ON ag.id_actividad = a.id_actividad
INNER JOIN grupo         g  ON ag.id_grupo = g.id_grupo
INNER JOIN ciclo_escolar ce ON ag.id_ciclo = ce.id_ciclo
WHERE ag.id_actividad_grupo = 'd4e5f6a7-b8c9-0123-defa-234567890123';
```
Por grupo y ciclo (actividades asignadas a un grupo):
```sql
SELECT a.nombre, tt.tipo, ag.fecha_de_entrega, c.nombre AS profesor
FROM actividad_grupo ag
INNER JOIN actividad  a  ON ag.id_actividad = a.id_actividad
INNER JOIN tipo_tarea tt ON a.id_tipo_tarea = tt.id_tipo_tarea
INNER JOIN cuenta     c  ON a.id_cuenta_profesor = c.id_cuenta
WHERE ag.id_grupo = 1 AND ag.id_ciclo = 4;
```
Grupos a los que se asignó una actividad:
```sql
SELECT g.nombre_grupo, ce.periodo, ag.fecha_de_entrega
FROM actividad_grupo ag
INNER JOIN grupo         g  ON ag.id_grupo = g.id_grupo
INNER JOIN ciclo_escolar ce ON ag.id_ciclo = ce.id_ciclo
WHERE ag.id_actividad = 'c3d4e5f6-a7b8-9012-cdef-123456789012';
```

#### UPDATE
```sql
UPDATE actividad_grupo
SET fecha_de_entrega = '2025-03-20 23:59:00'
WHERE id_actividad_grupo = 'd4e5f6a7-b8c9-0123-defa-234567890123';
```

#### DELETE
```sql
DELETE FROM actividad_grupo
WHERE id_actividad_grupo = 'd4e5f6a7-b8c9-0123-defa-234567890123';
```

---

### Entidad: comentario

#### CREATE
Público:
```sql
INSERT INTO comentario (id_comentario, contenido, privado, id_cuenta, id_actividad)
VALUES (UUID(), '¿El diagrama debe incluir las vistas?', FALSE,
        'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
        'c3d4e5f6-a7b8-9012-cdef-123456789012');
```
Privado (profesor-alumno):
```sql
INSERT INTO comentario (id_comentario, contenido, privado, id_cuenta, id_actividad)
VALUES (UUID(), 'Tu entrega tiene errores en la 3FN, revisa ciclo_cuenta', TRUE,
        'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
        'c3d4e5f6-a7b8-9012-cdef-123456789012');
```

#### READ
Todos:
```sql
SELECT co.id_comentario, c.nombre, co.contenido, co.privado, co.created_at
FROM comentario co
INNER JOIN cuenta c ON co.id_cuenta = c.id_cuenta;
-- INNER JOIN: todo comentario DEBE tener una cuenta
```
Por tipo (público o privado):
```sql
SELECT co.id_comentario, c.nombre, co.contenido, co.created_at
FROM comentario co
INNER JOIN cuenta c ON co.id_cuenta = c.id_cuenta
WHERE co.privado = FALSE; -- cambiar a TRUE para privados
```
Chat público de una actividad:
```sql
SELECT c.nombre, co.contenido, co.created_at
FROM comentario co
INNER JOIN cuenta c ON co.id_cuenta = c.id_cuenta
WHERE co.id_actividad = 'c3d4e5f6-a7b8-9012-cdef-123456789012'
  AND co.privado = FALSE
ORDER BY co.created_at ASC;
-- INNER JOIN: todo comentario DEBE tener una cuenta
```
Chat privado entre profesor y alumno:
```sql
SELECT c.nombre, co.contenido, co.created_at
FROM comentario co
INNER JOIN cuenta c ON co.id_cuenta = c.id_cuenta
WHERE co.id_actividad = 'c3d4e5f6-a7b8-9012-cdef-123456789012'
  AND co.privado = TRUE
  AND co.id_cuenta IN (
      'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
      'b2c3d4e5-f6a7-8901-bcde-f12345678901'
  )
ORDER BY co.created_at ASC;
```
Uno:
```sql
SELECT c.nombre, co.contenido, co.privado, co.created_at
FROM comentario co
INNER JOIN cuenta c ON co.id_cuenta = c.id_cuenta
WHERE co.id_comentario = 'e5f6a7b8-c9d0-1234-efab-345678901234';
```

#### UPDATE
```sql
UPDATE comentario
SET contenido = '¿El diagrama debe incluir vistas y procedimientos?'
WHERE id_comentario = 'e5f6a7b8-c9d0-1234-efab-345678901234';
```

#### DELETE
```sql
DELETE FROM comentario
WHERE id_comentario = 'e5f6a7b8-c9d0-1234-efab-345678901234';
```

---

### Entidad: entrega

#### CREATE
```sql
INSERT INTO entrega (id_entrega, id_actividad_grupo, id_cuenta, entregado, fecha_de_entrega)
VALUES (UUID(), 'd4e5f6a7-b8c9-0123-defa-234567890123',
        'a1b2c3d4-e5f6-7890-abcd-ef1234567890', TRUE, NOW());
```

#### READ
Todos:
```sql
SELECT e.id_entrega, c.nombre, a.nombre AS actividad, e.entregado, e.calificacion, e.fecha_de_entrega
FROM entrega e
INNER JOIN cuenta          c  ON e.id_cuenta = c.id_cuenta
INNER JOIN actividad_grupo ag ON e.id_actividad_grupo = ag.id_actividad_grupo
INNER JOIN actividad       a  ON ag.id_actividad = a.id_actividad;
-- INNER JOIN: toda entrega DEBE tener cuenta y actividad_grupo
```
Entregas de una actividad_grupo:
```sql
SELECT c.nombre, e.entregado, e.calificacion, e.fecha_de_entrega
FROM entrega e
INNER JOIN cuenta c ON e.id_cuenta = c.id_cuenta
WHERE e.id_actividad_grupo = 'd4e5f6a7-b8c9-0123-defa-234567890123';
-- INNER JOIN: toda entrega DEBE tener una cuenta
```
Alumnos que NO han entregado:
```sql
SELECT c.nombre
FROM ciclo_cuenta cc
INNER JOIN cuenta c ON cc.id_cuenta = c.id_cuenta
LEFT JOIN entrega e ON e.id_cuenta = cc.id_cuenta
                    AND e.id_actividad_grupo = 'd4e5f6a7-b8c9-0123-defa-234567890123'
WHERE cc.id_grupo = 1
  AND e.id_entrega IS NULL;
-- LEFT JOIN EXCLUDING: alumnos del grupo sin entrega
```
Historial de entregas de un alumno:
```sql
SELECT a.nombre, ag.fecha_de_entrega, e.entregado, e.calificacion
FROM entrega e
INNER JOIN actividad_grupo ag ON e.id_actividad_grupo = ag.id_actividad_grupo
INNER JOIN actividad       a  ON ag.id_actividad = a.id_actividad
WHERE e.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
ORDER BY ag.fecha_de_entrega DESC;
```
Una:
```sql
SELECT c.nombre, a.nombre AS actividad, e.entregado, e.calificacion, e.fecha_de_entrega
FROM entrega e
INNER JOIN cuenta          c  ON e.id_cuenta = c.id_cuenta
INNER JOIN actividad_grupo ag ON e.id_actividad_grupo = ag.id_actividad_grupo
INNER JOIN actividad       a  ON ag.id_actividad = a.id_actividad
WHERE e.id_entrega = 'f6a7b8c9-d0e1-2345-fabc-456789012345';
```

#### UPDATE
Calificar:
```sql
UPDATE entrega
SET calificacion = 85
WHERE id_entrega = 'f6a7b8c9-d0e1-2345-fabc-456789012345';
```
Marcar entregada:
```sql
UPDATE entrega
SET entregado = TRUE, fecha_de_entrega = NOW()
WHERE id_entrega = 'f6a7b8c9-d0e1-2345-fabc-456789012345';
```

#### DELETE
```sql
DELETE FROM entrega
WHERE id_entrega = 'f6a7b8c9-d0e1-2345-fabc-456789012345';
```

---

### Entidad: archivo

#### CREATE
Adjunto de actividad:
```sql
INSERT INTO archivo (id_archivo, nombre, ruta, mime_type, id_actividad)
VALUES (UUID(), 'instrucciones.pdf', '/uploads/actividades/instrucciones.pdf',
        'application/pdf', 'c3d4e5f6-a7b8-9012-cdef-123456789012');
```
Adjunto de entrega:
```sql
INSERT INTO archivo (id_archivo, nombre, ruta, mime_type, id_entrega)
VALUES (UUID(), 'diagrama_er.png', '/uploads/entregas/diagrama_er.png',
        'image/png', 'f6a7b8c9-d0e1-2345-fabc-456789012345');
```

#### READ
Todos:
```sql
SELECT ar.id_archivo, ar.nombre, ar.mime_type, e.id_entrega, a.nombre AS actividad
FROM archivo ar
LEFT JOIN entrega   e ON ar.id_entrega   = e.id_entrega
LEFT JOIN actividad a ON ar.id_actividad = a.id_actividad;
-- LEFT JOIN: un archivo puede ser de actividad o de entrega, no necesariamente ambos
```
Uno:
```sql
SELECT ar.id_archivo, ar.nombre, ar.ruta, ar.mime_type, ar.created_at
FROM archivo ar
WHERE ar.id_archivo = 'a7b8c9d0-e1f2-3456-abcd-567890123456';
```
Archivos de una actividad:
```sql
SELECT nombre, ruta, mime_type, created_at
FROM archivo
WHERE id_actividad = 'c3d4e5f6-a7b8-9012-cdef-123456789012';
```
Archivos de una entrega:
```sql
SELECT nombre, ruta, mime_type, created_at
FROM archivo
WHERE id_entrega = 'f6a7b8c9-d0e1-2345-fabc-456789012345';
```
Actividades sin archivos adjuntos:
```sql
SELECT a.nombre
FROM actividad a
LEFT JOIN archivo ar ON a.id_actividad = ar.id_actividad
WHERE ar.id_archivo IS NULL;
-- LEFT JOIN EXCLUDING: actividades sin material adjunto
```

#### UPDATE
```sql
UPDATE archivo
SET nombre = 'nuevo_nombre.pdf'
WHERE id_archivo = 'a7b8c9d0-e1f2-3456-abcd-567890123456';
```

#### DELETE
```sql
DELETE FROM archivo
WHERE id_archivo = 'a7b8c9d0-e1f2-3456-abcd-567890123456';
```

---

### Entidad: cuestionario

#### CREATE
```sql
INSERT INTO cuestionario (id_cuestionario, enlace, id_ciclo_cuenta)
VALUES (UUID(), 'https://forms.gle/abc123xyz',
        'b2c3d4e5-f6a7-8901-bcde-f12345678901');
```

#### READ
Todos:
```sql
SELECT cu.id_cuestionario, cu.enlace, c.nombre AS alumno, cu.created_at
FROM cuestionario cu
INNER JOIN ciclo_cuenta cc ON cu.id_ciclo_cuenta = cc.id_ciclo_cuenta
INNER JOIN cuenta       c  ON cc.id_cuenta = c.id_cuenta;
-- INNER JOIN: toda FK es obligatoria
```
Cuestionarios de un grupo en un ciclo:
```sql
SELECT cu.enlace, cu.created_at, c.nombre AS alumno
FROM cuestionario cu
INNER JOIN ciclo_cuenta cc ON cu.id_ciclo_cuenta = cc.id_ciclo_cuenta
INNER JOIN cuenta       c  ON cc.id_cuenta = c.id_cuenta
WHERE cc.id_grupo = 1 AND cc.id_ciclo = 4;
-- INNER JOIN: toda FK es obligatoria
```
Por alumno:
```sql
SELECT cu.enlace, cu.created_at
FROM cuestionario cu
INNER JOIN ciclo_cuenta cc ON cu.id_ciclo_cuenta = cc.id_ciclo_cuenta
WHERE cc.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```
Uno:
```sql
SELECT cu.enlace, cu.created_at, c.nombre AS alumno
FROM cuestionario cu
INNER JOIN ciclo_cuenta cc ON cu.id_ciclo_cuenta = cc.id_ciclo_cuenta
INNER JOIN cuenta       c  ON cc.id_cuenta = c.id_cuenta
WHERE cu.id_cuestionario = 'b8c9d0e1-f2a3-4567-bcde-678901234567';
```
Grupos sin cuestionario en un ciclo:
```sql
SELECT g.nombre_grupo
FROM grupo g
LEFT JOIN ciclo_cuenta cc ON g.id_grupo = cc.id_grupo AND cc.id_ciclo = 4
LEFT JOIN cuestionario cu ON cc.id_ciclo_cuenta = cu.id_ciclo_cuenta
WHERE cu.id_cuestionario IS NULL AND g.id_ciclo = 4;
-- LEFT JOIN EXCLUDING: grupos sin cuestionario asignado
```

#### UPDATE
```sql
UPDATE cuestionario
SET enlace = 'https://forms.gle/nuevo456abc'
WHERE id_cuestionario = 'b8c9d0e1-f2a3-4567-bcde-678901234567';
```

#### DELETE
```sql
DELETE FROM cuestionario
WHERE id_cuestionario = 'b8c9d0e1-f2a3-4567-bcde-678901234567';
```

---

### Entidad: retroalimentacion_cuestionario

#### CREATE
```sql
INSERT INTO retroalimentacion_cuestionario
    (id_retroalimentacion, id_cuestionario, id_ciclo_cuenta, respuesta)
VALUES (UUID(), 'b8c9d0e1-f2a3-4567-bcde-678901234567',
        'b2c3d4e5-f6a7-8901-bcde-f12345678901',
        'El alumno comprende el tema pero necesita reforzar normalización');
```

#### READ
Todos:
```sql
SELECT rc.id_retroalimentacion, c.nombre AS alumno, rc.respuesta, rc.created_at
FROM retroalimentacion_cuestionario rc
INNER JOIN ciclo_cuenta cc ON rc.id_ciclo_cuenta = cc.id_ciclo_cuenta
INNER JOIN cuenta       c  ON cc.id_cuenta = c.id_cuenta;
-- INNER JOIN: toda retroalimentacion DEBE tener cuenta y cuestionario
```
Uno:
```sql
SELECT rc.id_retroalimentacion, c.nombre AS alumno, rc.respuesta, rc.created_at
FROM retroalimentacion_cuestionario rc
INNER JOIN ciclo_cuenta cc ON rc.id_ciclo_cuenta = cc.id_ciclo_cuenta
INNER JOIN cuenta       c  ON cc.id_cuenta = c.id_cuenta
WHERE rc.id_retroalimentacion = 'c9d0e1f2-a3b4-5678-cdef-789012345678';
```
Respuestas de un cuestionario:
```sql
SELECT c.nombre AS alumno, rc.respuesta, rc.created_at
FROM retroalimentacion_cuestionario rc
INNER JOIN ciclo_cuenta cc ON rc.id_ciclo_cuenta = cc.id_ciclo_cuenta
INNER JOIN cuenta       c  ON cc.id_cuenta = c.id_cuenta
WHERE rc.id_cuestionario = 'b8c9d0e1-f2a3-4567-bcde-678901234567';
-- INNER JOIN: toda retroalimentacion DEBE tener cuenta y cuestionario
```
Por alumno:
```sql
SELECT rc.respuesta, rc.created_at
FROM retroalimentacion_cuestionario rc
INNER JOIN ciclo_cuenta cc ON rc.id_ciclo_cuenta = cc.id_ciclo_cuenta
WHERE cc.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```
Alumnos sin respuesta en un cuestionario:
```sql
SELECT c.nombre
FROM ciclo_cuenta cc
INNER JOIN cuenta c ON cc.id_cuenta = c.id_cuenta
LEFT JOIN retroalimentacion_cuestionario rc
       ON rc.id_ciclo_cuenta = cc.id_ciclo_cuenta
      AND rc.id_cuestionario = 'b8c9d0e1-f2a3-4567-bcde-678901234567'
WHERE cc.id_grupo = 1
  AND rc.id_retroalimentacion IS NULL;
-- LEFT JOIN EXCLUDING: alumnos sin respuesta registrada
```

#### UPDATE
```sql
UPDATE retroalimentacion_cuestionario
SET respuesta = 'Excelente comprensión, sin observaciones'
WHERE id_retroalimentacion = 'c9d0e1f2-a3b4-5678-cdef-789012345678';
```

#### DELETE
```sql
DELETE FROM retroalimentacion_cuestionario
WHERE id_retroalimentacion = 'c9d0e1f2-a3b4-5678-cdef-789012345678';
```

---

### Entidad: retroalimentacion_alumno

#### CREATE
```sql
INSERT INTO retroalimentacion_alumno
    (id_actividad_grupo, id_cuenta, valoracion, pregunta1, pregunta2, pregunta3, pregunta4)
VALUES ('d4e5f6a7-b8c9-0123-defa-234567890123',
        'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
        4, TRUE, FALSE, TRUE, FALSE);
```

#### READ
Todos:
```sql
SELECT ra.id_actividad_grupo, c.nombre, ra.valoracion, ra.pregunta1, ra.pregunta2, ra.pregunta3, ra.pregunta4
FROM retroalimentacion_alumno ra
INNER JOIN cuenta c ON ra.id_cuenta = c.id_cuenta;
-- INNER JOIN: toda retroalimentacion DEBE tener una cuenta
```
Retroalimentación de una actividad:
```sql
SELECT c.nombre, r.valoracion, r.pregunta1, r.pregunta2, r.pregunta3, r.pregunta4
FROM retroalimentacion_alumno r
INNER JOIN cuenta c ON r.id_cuenta = c.id_cuenta
WHERE r.id_actividad_grupo = 'd4e5f6a7-b8c9-0123-defa-234567890123';
-- INNER JOIN: toda retroalimentacion DEBE tener una cuenta
```
Por criterio (ej. alumnos que reportaron dudas):
```sql
SELECT c.nombre, r.valoracion
FROM retroalimentacion_alumno r
INNER JOIN cuenta c ON r.id_cuenta = c.id_cuenta
INNER JOIN actividad_grupo ag ON r.id_actividad_grupo = ag.id_actividad_grupo
WHERE r.pregunta3 = TRUE
  AND ag.id_ciclo = 4;
```
Uno (retroalimentación de un alumno en una actividad):
```sql
SELECT c.nombre, r.valoracion, r.pregunta1, r.pregunta2, r.pregunta3, r.pregunta4
FROM retroalimentacion_alumno r
INNER JOIN cuenta c ON r.id_cuenta = c.id_cuenta
WHERE r.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
  AND r.id_actividad_grupo = 'd4e5f6a7-b8c9-0123-defa-234567890123';
```
Alumnos sin retroalimentación en una actividad:
```sql
SELECT c.nombre
FROM ciclo_cuenta cc
INNER JOIN cuenta c ON cc.id_cuenta = c.id_cuenta
LEFT JOIN retroalimentacion_alumno ra
       ON ra.id_cuenta = cc.id_cuenta
      AND ra.id_actividad_grupo = 'd4e5f6a7-b8c9-0123-defa-234567890123'
WHERE cc.id_grupo = 1
  AND ra.id_cuenta IS NULL;
-- LEFT JOIN EXCLUDING: alumnos sin retroalimentacion registrada
```

#### UPDATE
```sql
UPDATE retroalimentacion_alumno
SET valoracion = 5, pregunta1 = TRUE, pregunta2 = TRUE,
    pregunta3 = FALSE, pregunta4 = TRUE
WHERE id_actividad_grupo = 'd4e5f6a7-b8c9-0123-defa-234567890123'
  AND id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

#### DELETE
```sql
DELETE FROM retroalimentacion_alumno
WHERE id_actividad_grupo = 'd4e5f6a7-b8c9-0123-defa-234567890123'
  AND id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

---

## KPIs

---

### KPI ① — Tasa de entrega por alumno en un ciclo

Mide qué porcentaje de actividades entregó cada alumno. Un alumno con menos del 60% es señal temprana de riesgo.

```sql
SELECT
    c.nombre,
    COUNT(ag.id_actividad_grupo)                             AS total_actividades,
    COUNT(e.id_entrega)                                      AS entregadas,
    ROUND(COUNT(e.id_entrega) * 100.0 /
          NULLIF(COUNT(ag.id_actividad_grupo), 0), 1)        AS porcentaje_entrega
FROM ciclo_cuenta cc
    INNER JOIN cuenta          c  ON cc.id_cuenta = c.id_cuenta
    INNER JOIN actividad_grupo ag ON ag.id_grupo  = cc.id_grupo
                                  AND ag.id_ciclo = cc.id_ciclo
    LEFT JOIN entrega          e  ON e.id_actividad_grupo = ag.id_actividad_grupo
                                  AND e.id_cuenta = cc.id_cuenta
    -- LEFT JOIN: alumnos sin entregas también aparecen con 0%
WHERE cc.id_ciclo = 4
GROUP BY c.id_cuenta, c.nombre
ORDER BY porcentaje_entrega ASC;
```

---

### KPI ② — Promedio de calificación por alumno

Detecta rendimiento académico individual. Complementa la tasa de entrega — un alumno puede entregar todo pero con calificaciones bajas.

```sql
SELECT
    c.nombre,
    ROUND(AVG(e.calificacion), 1)  AS promedio,
    COUNT(e.id_entrega)            AS entregas_calificadas,
    COUNT(ag.id_actividad_grupo)   AS total_actividades
FROM ciclo_cuenta cc
    INNER JOIN cuenta          c  ON cc.id_cuenta = c.id_cuenta
    INNER JOIN actividad_grupo ag ON ag.id_grupo  = cc.id_grupo
                                  AND ag.id_ciclo = cc.id_ciclo
    LEFT JOIN entrega          e  ON e.id_actividad_grupo = ag.id_actividad_grupo
                                  AND e.id_cuenta = cc.id_cuenta
    -- LEFT JOIN: incluimos aunque no tengan calificaciones aún
WHERE cc.id_ciclo = 4
GROUP BY c.id_cuenta, c.nombre
ORDER BY promedio ASC;
```

---

### KPI ③ — Alumnos en riesgo de deserción

Combina tasa de entrega baja Y promedio bajo. Los alumnos que cumplen ambas condiciones son prioridad de atención inmediata.

```sql
SELECT
    c.nombre,
    ROUND(COUNT(e.id_entrega) * 100.0 /
          NULLIF(COUNT(ag.id_actividad_grupo), 0), 1) AS porcentaje_entrega,
    ROUND(AVG(e.calificacion), 1)                     AS promedio
FROM ciclo_cuenta cc
    INNER JOIN cuenta          c  ON cc.id_cuenta = c.id_cuenta
    INNER JOIN actividad_grupo ag ON ag.id_grupo  = cc.id_grupo
                                  AND ag.id_ciclo = cc.id_ciclo
    LEFT JOIN entrega          e  ON e.id_actividad_grupo = ag.id_actividad_grupo
                                  AND e.id_cuenta = cc.id_cuenta
    -- LEFT JOIN: alumnos sin entregas son los más en riesgo
WHERE cc.id_ciclo = 4
GROUP BY c.id_cuenta, c.nombre
HAVING porcentaje_entrega < 60
   AND (promedio < 60 OR promedio IS NULL)
ORDER BY porcentaje_entrega ASC;
```

---

### KPI ④ — Tasa de entrega por grupo

Permite comparar grupos entre sí dentro del mismo ciclo y detectar si el problema es individual o grupal.

```sql
SELECT
    g.nombre_grupo,
    t.turno,
    COUNT(DISTINCT cc.id_cuenta)                             AS total_alumnos,
    COUNT(e.id_entrega)                                      AS entregas_realizadas,
    COUNT(ag.id_actividad_grupo)                             AS total_actividades,
    ROUND(COUNT(e.id_entrega) * 100.0 /
          NULLIF(COUNT(ag.id_actividad_grupo), 0), 1)        AS porcentaje_entrega
FROM grupo g
    INNER JOIN turno           t  ON g.id_turno  = t.id_turno
    INNER JOIN actividad_grupo ag ON ag.id_grupo = g.id_grupo
    INNER JOIN ciclo_cuenta    cc ON cc.id_grupo = g.id_grupo
                                  AND cc.id_ciclo = ag.id_ciclo
    LEFT JOIN entrega          e  ON e.id_actividad_grupo = ag.id_actividad_grupo
                                  AND e.id_cuenta = cc.id_cuenta
    -- LEFT JOIN: grupos con cero entregas también aparecen
WHERE g.id_ciclo = 4
GROUP BY g.id_grupo, g.nombre_grupo, t.turno
ORDER BY porcentaje_entrega ASC;
```

---

### KPI ⑤ — Comparativa matutino vs vespertino

Detecta si el turno es un factor de riesgo. Históricamente el vespertino tiene mayor deserción por carga laboral de los alumnos.

```sql
SELECT
    t.turno,
    COUNT(DISTINCT cc.id_cuenta)                             AS total_alumnos,
    ROUND(AVG(e.calificacion), 1)                            AS promedio_calificacion,
    ROUND(COUNT(e.id_entrega) * 100.0 /
          NULLIF(COUNT(ag.id_actividad_grupo), 0), 1)        AS porcentaje_entrega
FROM grupo g
    INNER JOIN turno           t  ON g.id_turno  = t.id_turno
    INNER JOIN actividad_grupo ag ON ag.id_grupo = g.id_grupo
    INNER JOIN ciclo_cuenta    cc ON cc.id_grupo = g.id_grupo
                                  AND cc.id_ciclo = ag.id_ciclo
    LEFT JOIN entrega          e  ON e.id_actividad_grupo = ag.id_actividad_grupo
                                  AND e.id_cuenta = cc.id_cuenta
WHERE g.id_ciclo = 4
GROUP BY t.id_turno, t.turno;
```

---

### KPI ⑥ — Rendimiento por tipo de aprendizaje

Detecta si algún estilo de aprendizaje tiene peor desempeño — señal de que los materiales o actividades no están adaptados para ese perfil.

```sql
SELECT
    ta.tipo                       AS estilo_aprendizaje,
    COUNT(DISTINCT ac.id_cuenta)  AS total_alumnos,
    ROUND(AVG(e.calificacion), 1) AS promedio_calificacion,
    ROUND(COUNT(e.id_entrega) * 100.0 /
          NULLIF(COUNT(ag.id_actividad_grupo), 0), 1) AS porcentaje_entrega
FROM aprendizaje_cuenta ac
    INNER JOIN tipo_aprendizaje ta ON ac.id_tipo_aprendizaje = ta.id_tipo_aprendizaje
    INNER JOIN ciclo_cuenta     cc ON cc.id_cuenta  = ac.id_cuenta
    INNER JOIN actividad_grupo  ag ON ag.id_grupo   = cc.id_grupo
                                   AND ag.id_ciclo  = cc.id_ciclo
    LEFT JOIN entrega           e  ON e.id_actividad_grupo = ag.id_actividad_grupo
                                   AND e.id_cuenta = ac.id_cuenta
WHERE cc.id_ciclo = 4
GROUP BY ta.id_tipo_aprendizaje, ta.tipo
ORDER BY promedio_calificacion ASC;
```

---

### KPI ⑦ — Actividades en clase vs Tareas en casa

Compara rendimiento entre los dos tipos. Si las tareas tienen calificación mucho menor, puede indicar que los alumnos no tienen apoyo en casa o trabajan fuera.

```sql
SELECT
    tt.tipo,
    COUNT(DISTINCT ag.id_actividad_grupo) AS total_asignaciones,
    ROUND(AVG(e.calificacion), 1)         AS promedio_calificacion,
    ROUND(COUNT(e.id_entrega) * 100.0 /
          NULLIF(COUNT(ag.id_actividad_grupo), 0), 1) AS porcentaje_entrega
FROM actividad_grupo ag
    INNER JOIN actividad  a  ON ag.id_actividad   = a.id_actividad
    INNER JOIN tipo_tarea tt ON a.id_tipo_tarea   = tt.id_tipo_tarea
    LEFT JOIN entrega     e  ON e.id_actividad_grupo = ag.id_actividad_grupo
    -- LEFT JOIN: incluimos aunque nadie haya entregado
WHERE ag.id_ciclo = 4
GROUP BY tt.id_tipo_tarea, tt.tipo;
```

---

### KPI ⑧ — Retroalimentación por actividad

Mide percepción del alumno sobre cada actividad. Si muchos reportan dudas (`pregunta3 = TRUE`) o tiempo insuficiente (`pregunta2 = FALSE`), la actividad necesita ajustarse.

```sql
SELECT
    a.nombre                                             AS actividad,
    tt.tipo                                              AS tipo_tarea,
    COUNT(r.id_cuenta)                                   AS total_respuestas,
    ROUND(AVG(r.valoracion), 1)                          AS valoracion_promedio,
    SUM(CASE WHEN r.pregunta1 = TRUE THEN 1 ELSE 0 END)  AS objetivos_cumplidos,
    SUM(CASE WHEN r.pregunta2 = TRUE THEN 1 ELSE 0 END)  AS tiempo_adecuado,
    SUM(CASE WHEN r.pregunta3 = TRUE THEN 1 ELSE 0 END)  AS tienen_dudas
FROM actividad_grupo ag
    INNER JOIN actividad  a  ON ag.id_actividad = a.id_actividad
    INNER JOIN tipo_tarea tt ON a.id_tipo_tarea = tt.id_tipo_tarea
    LEFT JOIN retroalimentacion_alumno r
           ON r.id_actividad_grupo = ag.id_actividad_grupo
    -- LEFT JOIN: actividades sin retroalimentacion también aparecen
WHERE ag.id_grupo = 1 AND ag.id_ciclo = 4
GROUP BY ag.id_actividad_grupo, a.nombre, tt.tipo
ORDER BY valoracion_promedio ASC;
```

---

### KPI ⑨ — Alumnos sin estilo de aprendizaje registrado

La falta del dato también es información. Si muchos alumnos no tienen estilo registrado, el cuestionario de diagnóstico no se está aplicando correctamente.

```sql
SELECT
    g.nombre_grupo,
    COUNT(cc.id_cuenta)                                      AS total_alumnos,
    COUNT(ac.id_cuenta)                                      AS con_estilo_registrado,
    COUNT(cc.id_cuenta) - COUNT(ac.id_cuenta)                AS sin_estilo_registrado,
    ROUND((COUNT(cc.id_cuenta) - COUNT(ac.id_cuenta)) * 100.0 /
          NULLIF(COUNT(cc.id_cuenta), 0), 1)                 AS porcentaje_sin_registrar
FROM ciclo_cuenta cc
    INNER JOIN grupo g ON cc.id_grupo = g.id_grupo
    LEFT JOIN aprendizaje_cuenta ac ON ac.id_cuenta = cc.id_cuenta
    -- LEFT JOIN: alumnos sin estilo aparecen con NULL
WHERE cc.id_ciclo = 4
GROUP BY g.id_grupo, g.nombre_grupo
ORDER BY porcentaje_sin_registrar DESC;
```

---

### KPI ⑩ — Entregas tardías por alumno

Un alumno que consistentemente entrega tarde puede estar sobrecargado o en riesgo. La fecha de entrega real vs la fecha límite lo revela.

```sql
SELECT
    c.nombre,
    COUNT(e.id_entrega)                                      AS total_entregas,
    SUM(CASE WHEN e.fecha_de_entrega > ag.fecha_de_entrega
             THEN 1 ELSE 0 END)                              AS entregas_tardias,
    ROUND(SUM(CASE WHEN e.fecha_de_entrega > ag.fecha_de_entrega
                   THEN 1 ELSE 0 END) * 100.0 /
          NULLIF(COUNT(e.id_entrega), 0), 1)                 AS porcentaje_tardias
FROM entrega e
    INNER JOIN cuenta          c  ON e.id_cuenta = c.id_cuenta
    INNER JOIN actividad_grupo ag ON e.id_actividad_grupo = ag.id_actividad_grupo
    -- INNER JOIN: toda entrega DEBE tener actividad_grupo y cuenta
WHERE ag.id_ciclo = 4
GROUP BY c.id_cuenta, c.nombre
ORDER BY porcentaje_tardias DESC;
```

---

### KPI ⑪ — Actividades sin ninguna entrega

La ausencia total de entregas en una actividad puede indicar que la actividad no fue comunicada correctamente o que hay un problema generalizado de comprensión.

```sql
SELECT
    a.nombre                AS actividad,
    g.nombre_grupo          AS grupo,
    ag.fecha_de_entrega,
    tt.tipo
FROM actividad_grupo ag
    INNER JOIN actividad  a  ON ag.id_actividad = a.id_actividad
    INNER JOIN tipo_tarea tt ON a.id_tipo_tarea = tt.id_tipo_tarea
    INNER JOIN grupo      g  ON ag.id_grupo     = g.id_grupo
    LEFT JOIN entrega     e  ON e.id_actividad_grupo = ag.id_actividad_grupo
    -- LEFT JOIN EXCLUDING: solo actividades sin ninguna entrega
WHERE ag.id_ciclo = 4
  AND e.id_entrega IS NULL
ORDER BY ag.fecha_de_entrega ASC;
```

---

### KPI ⑫ — Progreso del ciclo: entregas por semana

Muestra la tendencia de actividad a lo largo del ciclo. Una caída sostenida en entregas semana a semana es señal de deserción progresiva.

```sql
SELECT
    WEEK(e.fecha_de_entrega)                                 AS semana,
    COUNT(e.id_entrega)                                      AS total_entregas,
    COUNT(DISTINCT e.id_cuenta)                              AS alumnos_activos,
    ROUND(AVG(e.calificacion), 1)                            AS promedio_semana
FROM entrega e
    INNER JOIN actividad_grupo ag ON e.id_actividad_grupo = ag.id_actividad_grupo
WHERE ag.id_ciclo = 4
  AND e.fecha_de_entrega IS NOT NULL
GROUP BY WEEK(e.fecha_de_entrega)
ORDER BY semana ASC;
```

---

## Referencia: Tipos de JOIN

| Tipo de JOIN | Qué devuelve | Cuándo usarlo en el sistema |
| :--- | :--- | :--- |
| `INNER JOIN` | Solo registros con coincidencia en ambas tablas | Relaciones obligatorias: `cuenta → tipo_usuario`, `actividad → tipo_tarea`, `grupo → turno`. Si el dato debe existir en ambos lados |
| `LEFT JOIN` | Todos los de A, coincidan o no con B | KPIs y reportes donde el cero también es información: alumnos sin entregas, grupos sin actividades, cuentas sin estilo de aprendizaje |
| `RIGHT JOIN` | Todos los de B, coincidan o no con A | Prácticamente no se usa — se reordena y se usa LEFT JOIN |
| `FULL OUTER JOIN` | Todos de ambas tablas, coincidan o no | Auditorías de datos huérfanos. MariaDB no lo soporta nativamente, se simula con `UNION` |
| `LEFT JOIN EXCLUDING INNER` | Solo los de A que NO tienen par en B | Detectar alumnos sin estilo de aprendizaje, actividades sin entregas, grupos sin cuestionario asignado |
| `RIGHT JOIN EXCLUDING INNER` | Solo los de B que NO tienen par en A | Detectar entregas sin cuenta asociada, archivos sin actividad ni entrega |
| `FULL OUTER JOIN EXCLUDING INNER` | Solo los que no tienen par en ninguno de los dos lados | Auditoría completa de huérfanos en ambas direcciones simultáneamente |

| JOIN | Notación de conjuntos |
| :--- | :--- |
| `INNER JOIN` | A ∩ B |
| `LEFT JOIN` | A |
| `RIGHT JOIN` | B |
| `FULL OUTER JOIN` | A ∪ B |
| `LEFT JOIN EXCLUDING INNER` | A \ (A ∩ B) |
| `RIGHT JOIN EXCLUDING INNER` | B \ (A ∩ B) |
| `FULL OUTER JOIN EXCLUDING INNER` | A △ B |

**Regla práctica:** Si el `NULL` en el resultado es válido e informativo → `LEFT JOIN`. Si el `NULL` significaría un error de datos → `INNER JOIN`.

### Patrón mental para construir cualquier JOIN

```
1. ¿Qué tabla es mi base?           → FROM tabla_a
2. ¿Qué tabla necesito unir?        → [TIPO] JOIN tabla_b
3. ¿Por qué columna se unen?        → ON tabla_a.fk = tabla_b.pk
4. ¿Quiero excluir la intersección? → WHERE columna_del_otro_lado IS NULL
5. ¿Necesito ambos lados?           → UNION con el JOIN inverso
```
