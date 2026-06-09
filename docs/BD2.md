### Schema de Base de Datos

```sql
--  CATÁLOGOS

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

--  CUENTA

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

--  CICLO ESCOLAR Y GRUPOS

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
    FOREIGN KEY (id_turno) REFERENCES turno        (id_turno),
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

--  ACTIVIDADES

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
    FOREIGN KEY (id_actividad) REFERENCES actividad    (id_actividad),
    FOREIGN KEY (id_grupo)     REFERENCES grupo        (id_grupo),
    FOREIGN KEY (id_ciclo)     REFERENCES ciclo_escolar (id_ciclo)
);

--  COMENTARIOS

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

--  ENTREGAS Y ARCHIVOS

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

--  CUESTIONARIO Y RETROALIMENTACIÓN

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
    FOREIGN KEY (id_cuestionario) REFERENCES cuestionario (id_cuestionario),
    FOREIGN KEY (id_ciclo_cuenta) REFERENCES ciclo_cuenta (id_ciclo_cuenta)
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
### Consultas para operaciones CRUD (Create, Read, Update, Delete)

#### Entidad: tipo_usuario
##### CREATE
```sql
INSERT INTO tipo_usuario (rol) 
VALUES
	('Alumno'),
	('Profesor'),
	('Administrador');
```
##### READ
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
##### UPDATE
```sql
UPDATE tipo_usuario
SET rol='Docente'
WHERE id_tipo_usuario = 2
```
##### DELETE
```sql
DELETE FROM tipo_usuario
WHERE id_tipo_usuario = 2
```

#### Entidad: tipo_aprendizaje
##### CREATE
```sql
INSERT INTO tipo_aprendizaje (tipo) 
VALUES
	('Visual'),
	('Auditivo'),
	('Kinestésico'),
	('LecturaEscritura');
```
##### READ
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
##### UPDATE
```sql
UPDATE tipo_aprendizaje
SET tipo='Lectura / Escritura'
WHERE id_tipo_aprendizaje = 4
```
##### DELETE
```sql
DELETE FROM tipo_aprendizaje
WHERE id_tipo_aprendizaje = 5
```

#### Entidad: Turno
##### CREATE
```sql
INSERT INTO turno (turno)
VALUES
	('Matutino'),
	('Vespertino');

```
##### READ
Todos:
```sql
SELECT id_turno, tipo 
FROM turno;

```
Uno:
```sql
SELECT id_turno, tipo
FROM turno
WHERE id_turno = 2;

```
##### UPDATE
```sql
UPDATE turno
SET turno = 'Mañana'
WHERE id_turno = 1;

```
##### DELETE
```sql
DELETE FROM turno
WHERE id_turno = 1;

```

#### Entidad: tipo_tarea
##### CREATE
```sql
INSERT INTO tipo_tarea (tipo)
VALUES
	('Actividad'),
	('Tarea');
```
##### READ
Todos:
```sql
SELECT id_tipo_tarea, tipo
FROM tipo_tarea
```
Uno:
```sql
SELECT id_tipo_tarea, tipo
FROM tipo_tarea
WHERE id_tipo_tarea = 1;
```
##### UPDATE
```sql
UPDATE tipo_tarea
SET tipo= 'Actividad en Clase'
WHERE id_tipo_tarea = 1;
```
##### DELETE
```sql
DELETE FROM tipo_tarea
WHERE id_tipo_tarea = 1;
```

#### Entidad: ciclo_escolar
##### CREATE
```sql
INSERT INTO ciclo_escolar (periodo)
VALUES
	('2024-2025'),
	('2025-2026'),
	('2026-2027'),
	('2027-2028');

```
##### READ
Todos:
```sql
SELECT id_ciclo, periodo
FROM ciclo_escolar
```
Uno:
```sql
SELECT id_ciclo, periodo
FROM ciclo_escolar
WHERE id_ciclo = 1;
```
##### UPDATE
```sql
UPDATE ciclo_escolar
SET periodo = '2026-2027'
WHERE id_ciclo = 1;
```
##### DELETE
```sql
DELETE FROM ciclo_escolar 
WHERE id_ciclo = 1;
```

#### Entidad: cuenta
##### CREATE
```sql
INSERT INTO cuenta (id_cuenta, correo, nombre, contraseña, id_tipo_usuario)
VALUES (UUID(), 'correo@gmail.com', 'Nombre', 'hash', 1)
```
##### READ
Todos (sin aliases):
```sql
SELECT cuenta.id_cuenta, cuenta.correo, cuenta.nombre, tipo_usuario.rol, cuenta.created_at
FROM cuenta
INNER JOIN tipo_usuario t ON cuenta.id_tipo_usuario = tipo_usuario.id_tipo_usuario;
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
INNER JOIN tipo_usuario ON cuenta.id_tipo usuario = tipo_usuario.id_tipo_usuario 
WHERE tipo_usuario.rol ='Profesor';
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
##### UPDATE
```sql
UPDATE cuenta
SET nombre = 'Nuevo nombre', correo = 'nuevo_correo@gmail.com'
WHERE id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```
##### DELETE
```sql
DELETE FROM cuenta
WHERE id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```


#### Entidad: aprendizaje_cuenta
##### CREATE
```sql
    INSERT INTO aprendizaje_cuenta (id_cuenta, id_tipo_aprendizaje)
    VALUES ('a1b2c3d4-e5f6-7890-abcd-ef1234567890', 2);
```
##### READ
Todos:
```sql 
    SELECT a.id_cuenta t.tipo a.id_tipo_aprendizaje 
    FROM aprendizaje_cuenta a
    INNER JOIN cuenta c ON a.id_cuenta = c.id_cuenta
    INNER JOIN tipo_aprendizaje t ON a.id_tipo_aprendizaje = t.id_tipo_aprendizaje
```
Tipo
```sql
    SELECT a.id_cuenta, t.tipo, a.id_tipo_aprendizaje
    FROM aprendizaje_cuenta a
    INNER JOIN cuenta c ON a.id_cuenta = c.id_cuentae
    INNER JOIN tipo_aprendizaje t ON a.id_tipo_aprendizaje = t.id_tipo_aprendizaje
    WHERE t.tipo = 'visual';
```
Uno:
```sql
    SELECT a.id_cuenta, t.tipo, a.id_tipo_aprendizaje
    FROM aprendizaje_cuenta a
    INNER JOIN cuenta c ON a.id_cuenta = c.id_cuenta
    WHERE a.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
    INNER JOIN tipo_aprendizaje t ON a.id_tipo_aprendizaje = t.id_tipo_aprendizaje
```
##### UPDATE
```sql
    UPDATE aprendizaje_cuenta
    SET id_tipo_aprendizaje = 2
    WHERE id_cuenta = 'uuid-cuenta':


```
##### DELETE
```sql
    DELETE FROM aprendizaje_cuenta
    WHERE id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

#### Entidad: grupo
##### CREATE
```sql
    INSERT INTO grupo (nombre_grupo, id_turno, id_ciclo)
    VALUES ('63-A',1, 4);
```
##### READ
Todos:
```sql
    SELECT g.id_grupo, g.nombre_grupo, t.turno, c.periodo
    FROM grupo g
    INNER JOIN turno t
        ON g.id_turno = t.id_turno
    INNER JOIN ciclo_escolar c
        ON g.id_ciclo = c.id_ciclo
   

```
Tipo
```sql
    SELECT g.id_grupo, g.nombre_grupo, t.turno, c.periodo
    FROM grupo g
    INNER JOIN turno t ON g.id_turno = t.id_turno
    INNER JOIN ciclo_escolar c ON g.id_ciclo = c.id_ciclo
    WHERE t.turno = 'Matutino';


```
Uno:
```sql
    SELECT g.id_grupo, g.nombre_grupo, t.turno, c.periodo
    FROM grupo g
    INNER JOIN turno t
        ON g.id_turno = t.id_turno
    INNER JOIN ciclo_escolar c
        ON g.id_ciclo = c.id_ciclo
    WHERE g.id_grupo = 1;
```
##### UPDATE
```sql
    UPDATE grupo
    SET nombre_grupo = '63-B'
    WHERE id_grupo = 1;

```
##### DELETE
```sql
    DELETE FROM grupo
    WHERE id_grupo = 1;
```

#### Entidad: ciclo_cuenta
##### CREATE
```sql
    INSERT INTO ciclo_cuenta (id_cuenta, id_grupo, id_ciclo)
    VALUES ('a1b2c3d4-e5f6-7890-abcd-ef1234567890', 1, 3);
```
##### READ
Todos:
```sql
    SELECT cc.id_cuenta cc.id_grupo cc.id_ciclo
    FROM ciclo_cuenta cc
    INNER JOIN cuenta c ON cc.id_cuenta = c.id_cuenta
    INNER JOIN grupo g ON cc.id_grupo = g.id_grupo
    INNER JOIN ciclo ci on cc.id_cilo = ci.id_ciclo
```
Tipo
```sql
    SELECT cc.id_cuenta cc.id_grupo cc.id_ciclo
    FROM ciclo_cuenta cc
    INNER JOIN cuenta c ON cc.id_cuenta = c.id_cuenta
    INNER JOIN grupo g ON cc.id_grupo = g.id_grupo
    WHERE cc.grupo = 1
    INNER JOIN ciclo ci on cc.id_cilo = ci.id_ciclo
```
Uno:
```sql
    SELECT cc.id_cuenta cc.id_grupo cc.id_ciclo
    FROM ciclo_cuenta cc
    INNER JOIN cuenta c ON cc.id_cuenta = c.id_cuenta
    WHERE cc.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
    INNER JOIN grupo g ON cc.id_grupo = g.id_grupo
    INNER JOIN ciclo ci on cc.id_cilo = ci.id_ciclo
    
```
##### UPDATE
```sql
    UPDATE ciclo_cuenta
    SET id_grupo = 2
    WHERE id_cuenta = 'uuid-cuenta':
```
##### DELETE
```sql
    DELETE FROM aprendizaje_cuenta
    WHERE id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

#### Actividad
##### CREATE
```sql
    INSERT INTO actividad(id_actividad, nombre, descripcion, id_cuenta_profesor, id_tipo_tarea)
    VALUES
    (UUID(), 'Proyecto final', 'Codigo Allegro', 'uuid-profesor', 1);
 ```   

##### READ
Todos
 ```sql
    SELECT a.id_actividad, a.nombre, a.descripcion, c.nombre profesor, tt.tipo
    FROM actividad a 
    INNER JOIN cuenta c
        ON a.id_cuenta_profesor = c.id_cuenta
    INNER JOIN tipo_tarea tt 
        ON a.id_tipo_tarea = tt.id_tipo_tarea
 
 
 ```
Uno;
 ```sql
    SELECT a.id_actividad, a.nombre, a.descripcion, c.nombre profesor, tt.tipo
    FROM actividad a
    INNER JOIN cuenta c
        ON a.id_cuenta_profesor = c.id_cuenta
    INNER JOIN tipo_tarea tt
        ON a.id_tipo_tarea = tt.id_tipo_tarea
    WHERE a.id_actividad = 'uuid';
 
 ```

 #### UPDATE
```sql  
    UPDATE actividad
    SET 'Actividad entregada'
    WHERE id_cuenta = 'uuid-cuenta'
```


 ##### DELETE
  ```sql
    DELETE FROM actividad
    WHERE id_actividad = 'uuid';
 
 ```


#### Entidad: comentario
##### CREATE
```sql
    INSERT INTO comentario (id_comentario, contenido, privado, id_cuenta)
    VALUES (UUID(), 'Hola buenos dias', 0,'a1b2c3d4-e5f6-7890-abcd-ef1234567890');
```
##### READ
Todos:
```sql
    SELECT co.id_comentario co.contenido co.privado co.id_cuenta c.id_tipo_cuenta
    FROM comentario co
    INNER JOIN cuenta c ON co.id_cuenta = c.id_cuenta
```
Tipo
```sql
    SELECT co.id_comentario co.contenido co.privado co.id_cuenta c.id_tipo_cuenta
    FROM comentario co
    INNER JOIN cuenta c ON co.id_cuenta = c.id_cuenta
    WHERE co.privado = 1
```
Uno:
```sql
    SELECT co.id_comentario co.contenido co.privado co.id_cuenta c.id_tipo_cuenta
    FROM comentario co
    INNER JOIN cuenta c ON co.id_cuenta = c.id_cuenta
    WHERE co.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
```
##### UPDATE
```sql
    UPDATE comentario
    SET privado = 1
    WHERE id_cuenta = 'uuid-cuenta':
```
##### DELETE
```sql
    DELETE FROM comentario
    WHERE id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

#### Entidad: actividad_grrupo
##### CREATE
```sql
    INSERT INTO actividad_grupo (id_actividad_grupo, id_actividad, id_grupo, id_ciclo, fecha_de_entrega)
    VALUES (UUID(), 'a1b2c3d4-e5f6-7890-abcd-ef1234567890', 2, 1,'2026-06-12 10:00:00');
```
##### READ
Todos:
```sql
    SELECT a.id_actividad_grupo a.id_actividad a.id_grupo a.id_ciclo a.fecha_de_entrega ac.nombre
    FROM actividad_grupo a
    INNER JOIN actividad ac ON a.id_actividad = ac.id_actividad
    INNER JOIN grupo g ON a.id_grupo = g.id_grupo
    INNER JOIN ciclo ci ON a.id_ciclo = ci.id_ciclo
```
Tipo
```sql
    SELECT a.id_actividad_grupo a.id_actividad a.id_grupo a.id_ciclo a.fecha_de_entrega ac.nombre
    FROM actividad_grupo a
    INNER JOIN actividad ac ON a.id_actividad = ac.id_actividad
    INNER JOIN grupo g ON a.id_grupo = g.id_grupo
    INNER JOIN ciclo ci ON a.id_ciclo = ci.id_ciclo
    WHERE a.ciclo = 2
```
Uno:
```sql
    SELECT a.id_actividad_grupo a.id_actividad a.id_grupo a.id_ciclo a.fecha_de_entrega ac.nombre
    FROM actividad_grupo a
    INNER JOIN actividad ac ON a.id_actividad = ac.id_actividad
    WHERE a.id_actividad = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
    INNER JOIN grupo g ON a.id_grupo = g.id_grupo
    INNER JOIN ciclo ci ON a.id_ciclo = ci.id_ciclo
```
##### UPDATE
```sql
    UPDATE actividad_grupo
    SET fecha de entrega = '2026-06-13 10:00:00'
    WHERE id_actividad_grupo = 'uuid-actividad_grupo':
```
##### DELETE
```sql
    DELETE FROM actividad_grupo
    WHERE id_actividad_grupo = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

#### Entidad: entrega
##### CREATE
```sql
    INSERT INTO entrega (id_entrega, id_actividad_grupo, id_cuenta, entregado, calificacion)
    VALUES (UUID(), 'a1b2c3d4-e5f6-7890-abcd-ef1234567890', 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',0, 10);
```
##### READ
Todos:
```sql
    SELECT e.id_entrega e.id_actividad_grupo e.id_cuenta c.nombre e.entrergado e.created_at
    FROM entrega e
    INNER JOIN actividad_grupo ag ON e.id_actividad_grupo = ag.id_actividad_grupo
    INNER JOIN cuenta c ON e.id_cuenta = c.id_cuenta
```
Tipo
```sql
    SELECT e.id_entrega e.id_actividad_grupo e.id_cuenta c.nombre e.entrergado e.created_at
    FROM entrega e
    INNER JOIN actividad_grupo ag ON e.id_actividad_grupo = ag.id_actividad_grupo
    WHERE id_actividad_grupo = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
    INNER JOIN cuenta c ON e.id_cuenta = c.id_cuenta
```
Uno:
```sql
    SELECT e.id_entrega e.id_actividad_grupo e.id_cuenta c.nombre e.entrergado e.created_at
    FROM entrega e
    INNER JOIN actividad_grupo ag ON e.id_actividad_grupo = ag.id_actividad_grupo
    WHERE id_entrega = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
    INNER JOIN cuenta c ON e.id_cuenta = c.id_cuenta
```
##### UPDATE
```sql
    UPDATE entrega
    SET entregado = 1
    WHERE id_entrega = 'uuid-entrega':
```
##### DELETE
```sql
    DELETE FROM entrega
    WHERE id_entrega = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

#### ENTIDAD: ARCHIVO

#### CREATE
```sql
    INSERT INTO archivo (nombre, ruta, mime_type, id_entrega)
    VALUES('documento.pdf', '/files/doc.pdf');

```
#### READ
todos
```sql
    SELECT ar.id_archivo, ar.nombre, e.id_entrega, a.nombre archivo_de_actividad
    FROM archivo ar
    LEFT JOIN entrega e ON ar.id_entrega = e.id_entrega
    LEFT JOIN actividad a ON ar.id_actividad = a.id_actividad;
```
#### UPDATE
```sql
    UPDATE archivo SET nombre = 'nuevo_nombre.pdf' WHERE id_archivo = 'uuid-archivo';
    SET subidos = 1
    WHERE id_archivo = 'uuid-archivo'
```
#### DELETE 
```sql
    DELETE FROM archivo
    WHERE id_archivo = "uuid-archivo"

```
#### Entidad: cuestionario
##### CREATE
```sql
    INSERT INTO cuestionario (id_cuestionario, id_ciclo_cuenta, enlace)
    VALUES (UUID(), 'a1b2c3d4-e5f6-7890-abcd-ef1234567890', 'https://.....');
```
##### READ
Todos:
```sql
    SELECT cu.id_cuestionario cu.id_ciclo_cuenta cu.enlace
    FROM cuestionario cu
    INNER JOIN ciclo_cuenta cc ON cu.id_ciclo_cuenta = cc.id_actividad_grupo
```
Tipo
```sql
    SELECT cu.id_cuestionario cu.id_ciclo_cuenta cu.enlace
    FROM cuestionario cu
    INNER JOIN ciclo_cuenta cc ON cu.id_ciclo_cuenta = cc.id_actividad_grupo
    WHERE id_ciclo_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
```
Uno:
```sql
    SELECT cu.id_cuestionario cu.id_ciclo_cuenta cu.enlace
    FROM cuestionario cu
    INNER JOIN ciclo_cuenta cc ON cu.id_ciclo_cuenta = cc.id_actividad_grupo
    WHERE id_cuestionario = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
```
##### UPDATE
    ```sql
        UPDATE cuestionario
        SET enlace = 'https://.......'
        WHERE id_cuestionario = 'uuid-entrega':
    ```
##### DELETE
```sql
    DELETE FROM cuestionario
    WHERE id_cuestionario = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```
### ENTIDAD: retroalimentacion_cuestionario
### CREATE
```sql
    INSERT INTO retroalimentacion_cuestionario (id_)
    
```
### READ
```sql
    
```
### UPDATE
```sql
    UPDATE retroalimentacion_cuestionario
    SET ver retroalimentacion = 'Ver resultados de retroalimentacion'
    WHERE id_retroalimentacion_cuestionario = 'uuid-retroalimentacion_cuestionario'
    
```
### DELETE
```sql
    DELETE FROM retroalimentacion_cuestionario
    WHERE id_cuestionario = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890
    
```

#### Entidad: retroalimentacion_alumno
##### CREATE
```sql
    INSERT INTO retroalimentacion_alumno (id_actividad_grupo, id_cuenta, valoracion, pregunta1, pregunta2, pregunta3, pregunta4)
    VALUES ('a1b2c3d4-e5f6-7890-abcd-ef1234567890', 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',5,0,1,1,0);
```
##### READ
Todos:
```sql
    SELECT ra.id_actividad_grupo, ra.id_cuenta, ra.valoracion, ra.pregunta1, ra.pregunta2, ra.pregunta3, ra.pregunta4
    FROM ra
    INNER JOIN actividad_grupo ag ON ra.id_actividad_grupo = ag.id_actividad_grupo
    INNER JOIN cuenta c ON ra.id_cuenta = c.id_cuenta
```
Tipo
```sql
    SELECT ra.id_actividad_grupo, ra.id_cuenta, ra.valoracion, ra.pregunta1, ra.pregunta2, ra.pregunta3, ra.pregunta4
    FROM ra
    INNER JOIN actividad_grupo ag ON ra.id_actividad_grupo = ag.id_actividad_grupo
    INNER JOIN cuenta c ON ra.id_cuenta = c.id_cuenta
    WHERE ra.pregunta1 = 1
```
Uno:
```sql
    SELECT ra.id_actividad_grupo, ra.id_cuenta, ra.valoracion, ra.pregunta1, ra.pregunta2, ra.pregunta3, ra.pregunta4
    FROM ra
    INNER JOIN actividad_grupo ag ON ra.id_actividad_grupo = ag.id_actividad_grupo
    INNER JOIN cuenta c ON ra.id_cuenta = c.id_cuenta
    WHERE ra.id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'
```
##### UPDATE
```sql
    UPDATE retroalimentacion_alumno
    SET valoracion = 4,
        pregunta1 = 1,
        pregunta2 = 0,
        pregunta3 = 1,
        pregunta4 = 1,

    WHERE id_actividad_grupo = 'uuid-actividad-grupo' AND id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```
##### DELETE
```sql
    DELETE FROM retroalimentacion_alumno
    WHERE id_cuenta = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
```

### Consultas para obtener KPI's


---
### Tips & Tricks

#### Notación tabla.columna
Aliases de tabla
```sql
FROM cuenta c
```
c es un alias, un apodo corto para la tabla. En lugar de escribir `cuenta.id_cuenta` escribimos `c.id_cuenta`. Se define en el `FROM` y se usa en el resto de la query
#### ¿Cuándo usar PK o UNIQUE?
¿La entidad se relaciona con otras tablas?
1. Sí
	¿Se requiere de una combinación única de atributos?
	1. Sí
		Usar UNIQUE para validar la combinación única de atributos
	2. No
		Sin validaciones adicionales
2. No
	¿Se requiere de una combinación única de atributos?
	1. Sí
		Usar una PK compuesta por la N cantidad de atributos que tengan que ser únicos
	2. No
		Sin validaciones adicionales

#### ¿Cómo es un JOIN?
Un JOIN nos permite unir entidades entre sí, de diversas formas, según los datos que queramos recuperar. La sintáxis base siempre es la misma: 

```sql
SELECT columnas
FROM tabla_a
[TIPO] JOIN tabla_b ON tabla_a.columna = tabla_b.columna
```

**Tipos de JOIN**
1. LEFT JOIN: Devuelve todos los registros de la tabla izquierda, y los de la derecha *sólo* si hay coincidencia. Si no hay coincidencia, rellena con `NULL`.
2. RIGHT JOIN: Devuelve todos los datos de la derecha, coincidan o no con la izquierda. En la práctica casi nunca se usa porque puedes voltear el orden de las tablas y usar LEFT JOIN.
3. FULL OUTER JOIN: Devolver los registros de ambas tablas coincidan o no. 
4. LEFT JOIN EXCLUDING INNER JOIN: Devuelve todos los registros que coinciden en la izquierda y *NO* tienen par en B. Útil para detectar huérfanos.
5. RIGHT JOIN EXCLUDING INNER JOIN: Devuelve todos los registros que coinciden en la derecha y *NO* tienen par en A. Útil para detectar huérfanos.
6. INNER JOIN: Devuelve sólo los registros que coinciden en ambas tablas. Si no hay coincidencias, el registro desaparece.
7. FULL OUTER JOIN EXCLUIDING INNER JOIN: Devuelve todos los registros que existen en la izquierda + todos los registros que existen en la derecha, sin la intersección. Detecta huérfanos en ambas tablas simultáneamente. 

| JOIN                              | Notación de conjuntos     |
| --------------------------------- | ------------------------- |
| `INNER JOIN`                      | A ∩ B                     |
| `LEFT JOIN`                       | A ∪ (A ∩ B) = A           |
| `RIGHT JOIN`                      | B ∪ (A ∩ B) = B           |
| `FULL OUTER JOIN`                 | A ∪ B                     |
| `LEFT JOIN EXCLUDING INNER`       | A - B = A \ (A ∩ B)       |
| `RIGHT JOIN EXCLUDING INNER`      | B - A = B \ (A ∩ B)       |
| `FULL OUTER JOIN EXCLUDING INNER` | (A ∪ B) - (A ∩ B) = A △ B |

> `△` es la diferencia simétrica — lo que está en A o en B pero no en ambos.

#### ¿Cuándo usar cada JOIN?

|Situación|JOIN a usar|
|---|---|
|La FK es obligatoria y siempre existe|`INNER JOIN`|
|Quiero todos los registros aunque no tengan par|`LEFT JOIN`|
|Quiero detectar registros sin relación|`LEFT JOIN EXCLUDING INNER`|
|Auditoría de huérfanos en ambas tablas|`FULL OUTER JOIN EXCLUDING INNER`|

**Regla práctica:** Si el `NULL` en el resultado es válido e informativo → `LEFT JOIN`. Si el `NULL` significaría un error de datos → `INNER JOIN`.

---

#### ¿Cómo terminar la query según el JOIN?

**`INNER JOIN`** — no necesita nada extra, la condición del `ON` es suficiente:

```sql
SELECT c.nombre, t.rol
FROM cuenta c
INNER JOIN tipo_usuario t ON c.id_tipo_usuario = t.id_tipo_usuario;
```

**`LEFT JOIN`** — igual que INNER JOIN, el `ON` es suficiente. Los registros sin par aparecen con `NULL`:

```sql
SELECT c.nombre, e.calificacion
FROM cuenta c
LEFT JOIN entrega e ON c.id_cuenta = e.id_cuenta;
```

**`LEFT JOIN EXCLUDING INNER`** — necesita `WHERE columna_de_B IS NULL` al final:

```sql
SELECT c.nombre
FROM cuenta c
LEFT JOIN aprendizaje_cuenta ac ON c.id_cuenta = ac.id_cuenta
WHERE ac.id_cuenta IS NULL;
--    ^^^^^^^^^^^^^^^^^^^^^^^^ esto convierte LEFT en EXCLUDING
```

**`RIGHT JOIN`** — igual que LEFT JOIN, solo se invierten las tablas:

```sql
SELECT c.nombre, e.calificacion
FROM entrega e
RIGHT JOIN cuenta c ON e.id_cuenta = c.id_cuenta;
```

**`RIGHT JOIN EXCLUDING INNER`** — igual que LEFT EXCLUDING pero con `WHERE columna_de_A IS NULL`:

```sql
SELECT e.id_entrega
FROM cuenta c
RIGHT JOIN entrega e ON c.id_cuenta = e.id_cuenta
WHERE c.id_cuenta IS NULL;
--    ^^^^^^^^^^^^^^^^^^^^^^^^ esto convierte RIGHT en EXCLUDING
```

**`FULL OUTER JOIN`** — se simula con `UNION` de LEFT + RIGHT:

```sql
SELECT c.nombre, e.calificacion
FROM cuenta c
LEFT JOIN entrega e ON c.id_cuenta = e.id_cuenta
UNION
SELECT c.nombre, e.calificacion
FROM cuenta c
RIGHT JOIN entrega e ON c.id_cuenta = e.id_cuenta;
```

**`FULL OUTER JOIN EXCLUDING INNER`** — `UNION` de LEFT EXCLUDING + RIGHT EXCLUDING:

```sql
SELECT c.nombre, e.id_entrega
FROM cuenta c
LEFT JOIN entrega e ON c.id_cuenta = e.id_cuenta
WHERE e.id_entrega IS NULL
UNION
SELECT c.nombre, e.id_entrega
FROM cuenta c
RIGHT JOIN entrega e ON c.id_cuenta = e.id_cuenta
WHERE c.id_cuenta IS NULL;
```

---

#### Patrón mental para construir cualquier JOIN

```
1. ¿Qué tabla es mi base?          → FROM tabla_a
2. ¿Qué tabla necesito unir?       → [TIPO] JOIN tabla_b
3. ¿Por qué columna se unen?       → ON tabla_a.fk = tabla_b.pk
4. ¿Quiero excluir la intersección? → WHERE columna_del_otro_lado IS NULL
5. ¿Necesito ambos lados?          → UNION con el JOIN inverso
```