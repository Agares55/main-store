CREATE TABLE users (
    id            BIGSERIAL     PRIMARY KEY,
    firstname     VARCHAR(100)  NOT NULL,
    lastname      VARCHAR(100)  NOT NULL,
    ident_number  VARCHAR(20)   NOT NULL UNIQUE,
    mobile_phone  VARCHAR(20),
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password      VARCHAR(255)  NOT NULL,
    url_photo     TEXT,
    birth_date    DATE,
    status        BOOLEAN       NOT NULL DEFAULT TRUE,
    role          VARCHAR(20)   NOT NULL DEFAULT 'user',
    created_at    TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    updated_at    TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    deleted_at    TIMESTAMPTZ
);

INSERT INTO users (firstname, lastname, ident_number, email, password)
VALUES ('Joan', 'Ayala', '76576567', 'joan@gmail.com', '');

-- Actualización para bases de datos donde la tabla users ya existía:
ALTER TABLE users
ADD COLUMN IF NOT EXISTS role VARCHAR(20) NOT NULL DEFAULT 'user';

-- Para asignar rol admin a un usuario existente (reemplaza el correo):
-- UPDATE users SET role = 'admin' WHERE email = 'admin@correo.com';

CREATE TABLE IF NOT EXISTS products (
    id          BIGSERIAL      PRIMARY KEY,
    name        VARCHAR(150)   NOT NULL,
    description TEXT,
    price       NUMERIC(12, 2) NOT NULL DEFAULT 0,
    stock       INTEGER        NOT NULL DEFAULT 0,
    url_image   TEXT           DEFAULT 'images/product_default.png',
    active      BOOLEAN        NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMPTZ    NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ    NOT NULL DEFAULT NOW()
);