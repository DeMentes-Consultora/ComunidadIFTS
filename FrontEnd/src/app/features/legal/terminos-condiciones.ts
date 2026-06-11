import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-terminos-condiciones',
  standalone: true,
  imports: [CommonModule, RouterModule],
  template: `
    <main class="legal-page">
      <section class="legal-hero">
        <p class="legal-kicker">Comunidad IFTS</p>
        <h1>Terminos y Condiciones</h1>
        <p>
          El presente documento establece las condiciones aplicables al acceso, navegacion y uso de
          Comunidad IFTS, incluyendo sus modulos, servicios y espacios de interaccion.
        </p>
      </section>

      <section class="legal-card">
        <h2>1. Aceptacion y naturaleza del sitio</h2>
        <p>
          El ingreso, registro o uso de Comunidad IFTS implica la aceptacion plena de estos Terminos
          y Condiciones. Comunidad IFTS es una iniciativa administrada por alumnos y graduados y no
          reviste caracter oficial ni representa institucionalmente al Gobierno de la Ciudad de Buenos Aires.
        </p>
      </section>

      <section class="legal-card">
        <h2>2. Registro de usuarios y roles</h2>
        <p>
          La plataforma podra contemplar distintos perfiles de usuario, entre ellos cuentas
          institucionales y cuentas de alumnos o graduados. La persona usuaria declara que los datos
          suministrados son veraces, completos y actualizados, y asume responsabilidad por la guarda
          de sus credenciales y por toda actividad realizada desde su cuenta.
        </p>
      </section>

      <section class="legal-card">
        <h2>3. Uso del mapa y contenido generado</h2>
        <p>
          La informacion del mapa, novedades institucionales y demas contenidos publicados en la
          plataforma podra ser aportada por terceros. Comunidad IFTS procura mantener la informacion
          disponible y actualizada, aunque no garantiza la inexistencia de errores, omisiones,
          interrupciones o desactualizaciones en contenidos cargados por usuarios o instituciones.
        </p>
        <p>
          Se encuentra prohibida la publicacion de contenidos ilicitos, ofensivos, discriminatorios,
          engañosos, difamatorios, spam o informacion falsa. La administracion podra moderar,
          restringir, suspender o eliminar contenidos y accesos que vulneren estas condiciones o
          comprometan el funcionamiento normal de la plataforma.
        </p>
      </section>

      <section class="legal-card">
        <h2>4. Bolsa de trabajo y proyectos colaborativos</h2>
        <p>
          Comunidad IFTS actua exclusivamente como espacio de vinculacion entre usuarios,
          instituciones y terceros. En consecuencia, no garantiza la veracidad, vigencia, legalidad,
          idoneidad ni resultado de ofertas, postulaciones, contrataciones o acuerdos celebrados entre
          las partes a partir del uso de la plataforma.
        </p>
      </section>

      <section class="legal-card">
        <h2>5. Tienda y destino de los fondos</h2>
        <p>
          La tienda tiene por finalidad contribuir al sostenimiento de la infraestructura tecnologica,
          el hosting, el dominio y el mantenimiento general de Comunidad IFTS. La existencia de dicho
          modulo no modifica el caracter gratuito de las funcionalidades principales del sitio.
        </p>
      </section>

      <section class="legal-card">
        <h2>6. Compras, pagos, envios y devoluciones</h2>
        <p>
          Los pagos se procesan a traves de Mercado Pago. Todos los importes se expresan en pesos
          argentinos y corresponden al valor vigente informado al momento de la operacion.
        </p>
        <p>
          Los envios se realizan a domicilio mediante mensajeria privada. El plazo estimado de entrega
          es de hasta 72 horas habiles contadas desde la acreditacion del pago, sin perjuicio de
          demoras ocasionadas por terceros o por circunstancias extraordinarias.
        </p>
        <p>
          Solo se admitiran reclamos, cambios o revisiones por fallas de fabricacion o defectos
          comprobables del producto. La persona compradora dispone de 7 dias corridos desde la
          recepcion para informar la incidencia y solicitar su evaluacion.
        </p>
      </section>

      <section class="legal-card">
        <h2>7. Propiedad intelectual</h2>
        <p>
          Los nombres, logos, imagenes e identificaciones institucionales eventualmente utilizados en
          el sitio o en productos vinculados a la comunidad pertenecen a sus respectivos titulares. Su
          exhibicion o utilizacion con fines identificatorios no implica cesion, transferencia,
          autorizacion general ni reconocimiento de derechos a favor de terceros.
        </p>
      </section>

      <section class="legal-card">
        <h2>8. Proteccion de datos</h2>
        <p>
          El tratamiento de datos personales se realiza conforme a la legislacion argentina aplicable y
          a la Politica de Privacidad vigente. La persona usuaria podra consultar dicho documento para
          obtener informacion detallada sobre finalidades, bases de tratamiento y derechos aplicables.
        </p>
        <a class="legal-link" routerLink="/politica-privacidad">Ver Politica de Privacidad</a>
      </section>

      <section class="legal-card">
        <h2>9. Modificaciones y vigencia</h2>
        <p>
          Comunidad IFTS podra actualizar estos Terminos y Condiciones cuando resulte necesario para
          adecuarlos a cambios funcionales, operativos o normativos. Las nuevas versiones entraran en
          vigencia desde su publicacion en el sitio.
        </p>
      </section>

      <section class="legal-card">
        <h2>10. Contacto</h2>
        <p>
          Para consultas relacionadas con estos Terminos y Condiciones, privacidad, compras o reclamos,
          se encuentra habilitado el canal comunidadiftsinfo@gmail.com.
        </p>
      </section>

      <section class="legal-card legal-card-end">
        <h2>9. Contacto</h2>
        <p>La utilizacion continuada del sitio importara conocimiento y aceptacion de la version vigente.</p>
      </section>
    </main>
  `,
  styles: [`
    .legal-page {
      min-height: calc(100vh - 64px);
      padding: 96px 20px 40px;
      background:
        radial-gradient(circle at top right, rgba(0, 102, 51, 0.12), transparent 24%),
        linear-gradient(180deg, #f7fbf8 0%, #edf5f0 100%);
    }

    .legal-hero,
    .legal-card {
      max-width: 940px;
      margin: 0 auto 18px;
    }

    .legal-hero {
      text-align: center;
      padding: 26px 24px;
      border-radius: 22px;
      background: linear-gradient(135deg, #006633 0%, #004d26 100%);
      box-shadow: 0 18px 38px rgba(0, 77, 38, 0.18);
    }

    .legal-kicker {
      margin: 0;
      text-transform: uppercase;
      letter-spacing: 0.14em;
      font-size: 0.76rem;
      color: rgba(255, 255, 255, 0.82);
    }

    .legal-hero h1 {
      margin: 10px 0 12px;
      color: #ffffff;
      font-size: clamp(2rem, 3.5vw, 3rem);
    }

    .legal-hero p,
    .legal-card p {
      color: #475b4c;
      line-height: 1.65;
    }

    .legal-hero p {
      color: rgba(255, 255, 255, 0.88);
      margin: 0 auto;
      max-width: 760px;
    }

    .legal-card {
      background: rgba(255, 255, 255, 0.92);
      border: 1px solid rgba(44, 34, 22, 0.08);
      border-radius: 18px;
      padding: 22px;
      box-shadow: 0 12px 28px rgba(24, 30, 36, 0.08);
    }

    .legal-card h2 {
      margin: 0 0 10px;
      color: #1d2127;
      font-size: 1.2rem;
      padding-bottom: 8px;
      border-bottom: 1px solid rgba(0, 102, 51, 0.18);
    }

    .legal-link {
      display: inline-flex;
      align-items: center;
      margin-top: 4px;
      color: #006633;
      font-weight: 600;
      text-decoration: none;
    }

    .legal-link:hover {
      text-decoration: underline;
    }

    .legal-card-end p {
      margin: 0;
      font-weight: 600;
      color: #214b37;
    }

    @media (max-width: 768px) {
      .legal-page {
        padding: 86px 14px 28px;
      }

      .legal-card {
        padding: 18px;
      }
    }
  `],
})
export class TerminosCondiciones {}