import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-politica-privacidad',
  standalone: true,
  imports: [CommonModule, RouterModule],
  template: `
    <main class="legal-page">
      <section class="legal-hero">
        <p class="legal-kicker">Comunidad IFTS</p>
        <h1>Politica de Privacidad</h1>
        <p>
          El presente documento informa de que manera se recolectan, utilizan, almacenan y protegen
          los datos personales tratados en el ambito de Comunidad IFTS.
        </p>
      </section>

      <section class="legal-card">
        <h2>1. Marco legal</h2>
        <p>
          Comunidad IFTS trata los datos personales de conformidad con la Ley N° 25.326 de Proteccion
          de Datos Personales de la Republica Argentina y demas normativa complementaria que resulte aplicable.
        </p>
      </section>

      <section class="legal-card">
        <h2>2. Datos recolectados</h2>
        <p>
          Al registrarse o utilizar determinadas funcionalidades, la persona usuaria podra proporcionar
          nombre, apellido, DNI, fecha de nacimiento, telefono, correo electronico, institucion,
          carrera, año de cursada y demas informacion vinculada a su perfil, solicitudes o postulaciones.
        </p>
      </section>

      <section class="legal-card">
        <h2>3. Finalidad del tratamiento</h2>
        <p>Los datos se utilizan exclusivamente para:</p>
        <ul>
          <li>gestionar el alta, autenticacion y administracion de la cuenta;</li>
          <li>validar la pertenencia a la comunidad educativa cuando ello resulte necesario;</li>
          <li>habilitar postulaciones, comunicaciones y demas funcionalidades propias del sitio;</li>
          <li>facilitar comunicaciones relativas a servicios, proyectos, gestiones o soporte;</li>
          <li>atender consultas, reclamos y solicitudes vinculadas al uso de la plataforma.</li>
        </ul>
      </section>

      <section class="legal-card">
        <h2>4. Visibilidad y acceso a la informacion</h2>
        <p>
          Los datos de contacto no se ponen a disposicion del publico en general ni de terceros ajenos
          a la plataforma. Su acceso se limita a los supuestos estrictamente necesarios para el
          funcionamiento del sitio, la gestion de postulaciones o la administracion correspondiente.
        </p>
      </section>

      <section class="legal-card">
        <h2>5. Conservacion y seguridad</h2>
        <p>
          Comunidad IFTS adopta medidas razonables de seguridad para resguardar la informacion frente a
          accesos no autorizados, perdida, alteracion, uso indebido o divulgacion no permitida. Sin
          perjuicio de ello, ningun entorno tecnologico conectado a internet puede garantizar seguridad absoluta.
        </p>
      </section>

      <section class="legal-card">
        <h2>6. Cesion de datos</h2>
        <p>
          Los datos personales no se ceden a terceros con fines comerciales ajenos a la comunidad. En
          caso de resultar necesario compartir determinada informacion para la prestacion de una
          funcionalidad especifica del sitio, ello se realizara dentro de los limites compatibles con
          esta Politica de Privacidad y con la normativa vigente.
        </p>
      </section>

      <section class="legal-card">
        <h2>7. Derechos de acceso, rectificacion, cancelacion y oposicion</h2>
        <p>
          La persona titular de los datos podra ejercer en cualquier momento sus derechos de acceso,
          rectificacion, actualizacion, cancelacion u oposicion mediante solicitud enviada a comunidadiftsinfo@gmail.com.
        </p>
      </section>

      <section class="legal-card">
        <h2>8. Conservacion y actualizacion de la politica</h2>
        <p>
          La presente Politica de Privacidad se mantendra vigente mientras permanezca publicada en el
          sitio y podra ser actualizada cuando resulte necesario por cambios funcionales, operativos o normativos.
        </p>
      </section>

      <section class="legal-card">
        <h2>9. Consultas y contacto</h2>
        <p>
          Para consultas sobre privacidad, soporte o ejercicio de derechos vinculados al tratamiento de
          datos personales, se encuentra habilitado el canal comunidadiftsinfo@gmail.com.
        </p>
        <a class="legal-link" routerLink="/terminos-condiciones">Ver Terminos y Condiciones</a>
      </section>

      <section class="legal-card legal-card-end">
        <h2>8. Consultas y contacto</h2>
        <p>La permanencia en el uso de la plataforma supone conocimiento de la version vigente de esta politica.</p>
      </section>
    </main>
  `,
  styles: [`
    .legal-page {
      min-height: calc(100vh - 64px);
      padding: 96px 20px 40px;
      background:
        radial-gradient(circle at top left, rgba(0, 102, 51, 0.12), transparent 28%),
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
    .legal-card p,
    .legal-card li {
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

    .legal-card ul {
      margin: 10px 0 0;
      padding-left: 18px;
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
export class PoliticaPrivacidad {}