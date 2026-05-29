UPDATE carrousel
SET foto_perfil_public_id = NULL,
    foto_perfil_url = NULL,
    idUpdate = CURRENT_TIMESTAMP
WHERE id_carrousel = 3
  AND foto_perfil_public_id = 'ComunidadIFTS/carrusel/php6226_c9t5zf';