FORMULARIO DE CONTACTO:

Necesito crear el formulario de contacto para que nos puedan llegar consultas desde la app.
Básate en el formulario de contacto del proyecto ifst15 si puede ser.

PERFIL DE USUARIO:

Rol Alumno:
Primero quiero crear el perfil de usuario. Lo que van a ver los usuarios registrados es: del lado izquierdo como en el proyecto del ifts15, fíjate por favor, la foto de perfil, debajo los datos personales, el ifts al cual esta anotado, la carrera y el año a las que está cursando (todo esto está en la base de datos), del lado derecho que aparezcan las ofertas laborales a las que este postulado. Cuando la “oferta laboral sea deshabilitada debería desaparecer del perfil del usuario y si el usuario no quiere ver mas esa oferta a la cual se postulo que pueda eliminarla de su perfil(en la base de datos pasaria a estar cancelada=1,habilitada=0).

Rol AdministradorIfts (3):
Las instituciones registradas pueden ver: siguiendo el mismo formato del lado izquierdo la foto de perfil (será el logo de la institución), debajo los datos de la misma: email de contacto, dirección, si tiene teléfono de contacto. Con posibilidad de que pueda cambiar los mismos.Del lado derecho quiero poder ver la cantidad total de ofertas laborales publicadas por la institucion,cantidad total de postulantes, ofertas laborales publicadas con dos slide toggle ,una para deshabilitar o habilitar la oferta y otro para cancvelarla y que se oculte .

DASHBOARD Rol administradorIFTS:

Del lado derecho quiero: una tabla con los siguientes campos: titulo de ofertas laboral publicada por la institucion,fecha de publicacion,usuario,apellido, nombre y la posibilidad de descargar el CV del postulante.Tambien quiero que como en el dashboard aparezcan algunas estadisticas como: cantidad de ofertas publicadas,cantidad de postulantes total

BOLSAS DE TRABAJO:

Bueno quiero crear la bolsa de trabajo y que se maneje de esta manera:

Solo pueden subir “ofertas laborales” las instituciones registradas (IFTS).
Solo pueden ver las “ofertas laborales” los usuarios registrados como alumnos.
Las ofertas laborales deben ser gestionadas por un administrador de la comunidadifts , o sea, que debe revisarse para ser aceptada o habilitada para ser publicada en la app.
Aclaracion de logica en base de datos: Habilitado = 0,cancelado = 0 (en estado revision)
                                       habilitado = 1,cancelado = 0 (aceptada,publicada)
                                       habilitado = 0,cancelado = 1 (se da de baja la publicacion de la oferta laboral) 

Oferta Laboral:

Crear el formulario para la oferta laboral, que sea una card que traiga los datos del ifts que la quiere publicar (institucion y email de contacto) y dos campos de texto, uno para el título de la oferta laboral y otro para redactar la misma. Al hacer click en el boton publicar queda en estado de habilitacion o aprobacion como en el registro de usuario y le envia a la institucion un mail que lo informe. Al habilitar la publicación también se le enviara un mail a la institucion de la misma y se muestra en el endpoint correspondiente con una card que contendrá un botón de postúlate.

Postulacion a la oferta laboral:

El alumno registrado que quiera postularse debe hacer click en el boton de la card que dira “postulate”, ahi se abre un formulario que va a mostrar los datos del alumno que ya estan en la base de datos : Apellido, nombre ,email(usuario) y telefono si lo hay y a parte un input donde puede adjuntar su CV,  curriculum vitae.
Cuando termino de adjuntarlo en dicha card se habilitara el Boton postulate y al hacer clilck en el hace lo siguiente: 1- se guarda la postulacion en la base de datos con estos datos,id del usuario y id del la oferta laboral.2- se envia un mail a la institucion que publlico la oferta con el cv del postulante y la oferta a la cual se postulo.3- se envia un mail al postulante informandole que esta postulado y que en cuanto avanvance la postulacion se le avisara.

Features (Vista “Gestion de Ofertas Laborales”):

Se debe crear una gestion de “ofertas laborales” similar a la del registro de usuarios. La misma tendra una tabla que mostrara los siguientes campos : Institucion, ofertaLaboral, postulante(usuario) y un slide toggle para habilitar la oferta laboral en la pagina. Esto en la seccion pendientes de habilitacion o publicacion, luego al igual que en registro de usuarios quiero un boton que diga publicadas y muestre los siguientes campos:  Institución, oferta Laboral y postulaciones (que va a tener la cantidad de postulaciones que tiene esa oferta laboral en tiempo real).
