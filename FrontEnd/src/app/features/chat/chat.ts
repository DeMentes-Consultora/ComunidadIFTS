import { AfterViewInit, Component, ElementRef, OnDestroy, OnInit, ViewChild, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { AuthUser } from '../../shared/models/auth.model';
import { AuthService } from '../../shared/services/auth.service';
import { ChatMessage, ChatPresence, ForoChatInitError, ForoChatService } from '../../shared/services/foro-chat.service';

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatFormFieldModule,
    MatInputModule,
    MatDividerModule,
    MatChipsModule
  ],
  templateUrl: './chat.html',
  styleUrl: './chat.css'
})
export class ChatComponent implements OnInit, AfterViewInit, OnDestroy {
  private readonly foroService = inject(ForoChatService);
  private readonly authService = inject(AuthService);
  private readonly snackBar = inject(MatSnackBar);

  @ViewChild('mensajeInput') mensajeInput?: ElementRef<HTMLTextAreaElement>;
  @ViewChild('scrollContainer') scrollContainer?: ElementRef<HTMLDivElement>;

  mensajes: ChatMessage[] = [];
  presencia: ChatPresence[] = [];
  cargando = true;
  enviando = false;
  escribiendo = false;
  mostrarEmojis = false;
  mensaje = '';
  usuario: AuthUser | null = null;
  readonly emojisRapidos = ['😀', '😂', '😍', '🙌', '👍', '🔥', '👏', '💬', '📌', '🤝', '✨', '🙏'];
  private typingTimer: ReturnType<typeof setTimeout> | null = null;
  private readonly subscriptions: Array<{ unsubscribe: () => void }> = [];

  ngOnInit(): void {
    const userSubscription = this.authService.currentUser$.subscribe((user) => {
      this.usuario = user;
      if (user) {
        void this.initChat(user);
      }
    });

    this.subscriptions.push({ unsubscribe: () => userSubscription.unsubscribe() });
  }

  ngAfterViewInit(): void {
    this.scrollToBottom();
  }

  ngOnDestroy(): void {
    if (this.typingTimer) {
      clearTimeout(this.typingTimer);
    }

    if (this.usuario) {
      void this.foroService.markOffline(this.usuario);
    }

    this.subscriptions.forEach((subscription) => subscription.unsubscribe());
  }

  private async initChat(user: AuthUser): Promise<void> {
    try {
      await this.foroService.ensureAnonymousSession();
      this.foroService.startRealtimeSync();

      const mensajesSubscription = this.foroService.observeMessages().subscribe((messages) => {
        this.mensajes = messages;
        this.cargando = false;
        queueMicrotask(() => this.scrollToBottom());
      });

      const presenciaSubscription = this.foroService.observePresence().subscribe((presence) => {
        this.presencia = presence;
      });

      this.subscriptions.push({ unsubscribe: () => mensajesSubscription.unsubscribe() });
      this.subscriptions.push({ unsubscribe: () => presenciaSubscription.unsubscribe() });

      await this.foroService.setPresence(user, false);
    } catch (error) {
      this.cargando = false;
      const initError: ForoChatInitError = this.foroService.describeInitError(error);
      this.snackBar.open(`No se pudo conectar al chat en tiempo real: ${initError.message}`, 'Cerrar', {
        duration: 5000,
        panelClass: 'snackbar-error',
        horizontalPosition: 'center',
        verticalPosition: 'top'
      });
      console.error('Chat init error:', initError.code, initError.message, error);
    }
  }

  onMensajeChange(): void {
    if (!this.usuario) {
      return;
    }

    this.escribiendo = this.mensaje.trim().length > 0;

    if (this.typingTimer) {
      clearTimeout(this.typingTimer);
    }

    void this.foroService.setTypingState(this.usuario, this.escribiendo);

    this.typingTimer = setTimeout(() => {
      this.escribiendo = false;
      if (this.usuario) {
        void this.foroService.setTypingState(this.usuario, false);
      }
    }, 1200);
  }

  async cargarMensajes(): Promise<void> {
    this.cargando = true;
    if (this.usuario) {
      await this.initChat(this.usuario);
    }
  }

  insertarEmoji(emoji: string): void {
    this.mensaje = `${this.mensaje}${emoji}`;
    this.mostrarEmojis = false;
    this.onMensajeChange();
    queueMicrotask(() => this.mensajeInput?.nativeElement.focus());
  }

  toggleEmojiPicker(): void {
    this.mostrarEmojis = !this.mostrarEmojis;
  }

  get conectadosActivos(): ChatPresence[] {
    return this.presencia.filter((persona) => persona.online);
  }

  get usuariosEscribiendo(): ChatPresence[] {
    return this.presencia.filter((persona) => persona.typing && persona.userId !== this.usuario?.id_usuario);
  }

  get nombreMiUsuario(): string {
    if (!this.usuario) {
      return 'Invitado';
    }

    return `${this.usuario.nombre} ${this.usuario.apellido}`.trim();
  }

  esMiMensaje(mensaje: ChatMessage): boolean {
    return mensaje.senderId === this.usuario?.id_usuario;
  }

  fotoDe(mensaje: ChatMessage): string | null {
    return mensaje.senderPhotoUrl || null;
  }

  textoUltimaActividad(persona: ChatPresence): string {
    return persona.typing ? 'escribiendo...' : persona.online ? 'en línea' : 'reciente';
  }

  async enviarMensaje(): Promise<void> {
    if (this.enviando) {
      return;
    }

    const texto = this.mensaje.trim();
    if (!texto || !this.usuario) {
      return;
    }

    this.enviando = true;
    try {
      await this.foroService.publishMessage(this.usuario, texto);
      this.mensaje = '';
      this.escribiendo = false;
      await this.foroService.setTypingState(this.usuario, false);
      this.snackBar.open('Mensaje enviado.', 'Cerrar', {
        duration: 3000,
        panelClass: 'snackbar-success',
        horizontalPosition: 'center',
        verticalPosition: 'top'
      });
    } catch {
      this.snackBar.open('No se pudo enviar el mensaje.', 'Cerrar', {
        duration: 5000,
        panelClass: 'snackbar-error',
        horizontalPosition: 'center',
        verticalPosition: 'top'
      });
    } finally {
      this.enviando = false;
      queueMicrotask(() => this.mensajeInput?.nativeElement.focus());
    }
  }

  trackPorId(_: number, item: { id: string }): string {
    return item.id;
  }

  private scrollToBottom(): void {
    const container = this.scrollContainer?.nativeElement;
    if (!container) {
      return;
    }

    container.scrollTop = container.scrollHeight;
  }
}
