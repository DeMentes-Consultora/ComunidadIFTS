import { Injectable, inject } from '@angular/core';
import { Auth } from '@angular/fire/auth';
import { AuthUser } from '../models/auth.model';
import { BehaviorSubject, Observable } from 'rxjs';
import { signInAnonymously } from 'firebase/auth';
import { getDatabase, onChildAdded, onChildChanged, onChildRemoved, onDisconnect, push, ref, set, update } from 'firebase/database';

export interface ChatMessage {
  id: string;
  text: string;
  senderId: number;
  senderFirstName: string;
  senderLastName: string;
  senderDisplayName: string;
  senderPhotoUrl: string | null;
  senderRole: number;
  createdAtMs: number;
}

export interface ChatPresence {
  id: string;
  userId: number;
  firstName: string;
  lastName: string;
  displayName: string;
  photoUrl: string | null;
  role: number;
  online: boolean;
  typing: boolean;
  lastSeenMs: number;
}

export interface ForoChatInitError {
  code: string;
  message: string;
}

@Injectable({ providedIn: 'root' })
export class ForoChatService {
  private readonly firebaseAuth = inject(Auth);
  private readonly database = getDatabase();
  private readonly messagesPath = 'foro/chat/messages';
  private readonly presencePath = 'foro/chat/presence';
  private sessionPromise: Promise<void> | null = null;
  private syncStarted = false;
  private readonly messagesSubject = new BehaviorSubject<ChatMessage[]>([]);
  private readonly presenceSubject = new BehaviorSubject<ChatPresence[]>([]);
  private readonly messagesMap = new Map<string, ChatMessage>();
  private readonly presenceMap = new Map<string, ChatPresence>();

  ensureAnonymousSession(): Promise<void> {
    if (this.firebaseAuth.currentUser) {
      return Promise.resolve();
    }

    if (!this.sessionPromise) {
      this.sessionPromise = signInAnonymously(this.firebaseAuth)
        .then(() => undefined)
        .catch((error) => {
          this.sessionPromise = null;
          console.error('Firebase anonymous auth failed', error);
          throw error;
        });
    }

    return this.sessionPromise;
  }

  describeInitError(error: unknown): ForoChatInitError {
    const firebaseError = error as { code?: string; message?: string } | null;
    const code = firebaseError?.code ?? 'unknown';
    const message = firebaseError?.message ?? 'No fue posible conectar el chat.';

    if (code === 'auth/admin-restricted-operation') {
      return {
        code,
        message: 'Firebase Authentication anónimo no está habilitado en el proyecto.'
      };
    }

    if (code === 'permission-denied') {
      return {
        code,
        message: 'Realtime Database está rechazando lectura o escritura en la ruta del chat.'
      };
    }

    return {
      code,
      message
    };
  }

  startRealtimeSync(): void {
    if (this.syncStarted) {
      return;
    }

    this.syncStarted = true;

    const messagesRef = ref(this.database, this.messagesPath);
    onChildAdded(messagesRef, (snapshot) => {
      this.messagesMap.set(snapshot.key ?? snapshot.ref.key ?? String(Date.now()), this.buildMessage(snapshot.key ?? snapshot.ref.key ?? String(Date.now()), snapshot.val()));
      this.emitMessages();
    });

    onChildChanged(messagesRef, (snapshot) => {
      const id = snapshot.key ?? snapshot.ref.key ?? String(Date.now());
      this.messagesMap.set(id, this.buildMessage(id, snapshot.val()));
      this.emitMessages();
    });

    onChildRemoved(messagesRef, (snapshot) => {
      const id = snapshot.key ?? snapshot.ref.key ?? String(Date.now());
      this.messagesMap.delete(id);
      this.emitMessages();
    });

    const presenceRef = ref(this.database, this.presencePath);
    onChildAdded(presenceRef, (snapshot) => {
      const id = snapshot.key ?? snapshot.ref.key ?? String(Date.now());
      this.presenceMap.set(id, this.buildPresence(id, snapshot.val()));
      this.emitPresence();
    });

    onChildChanged(presenceRef, (snapshot) => {
      const id = snapshot.key ?? snapshot.ref.key ?? String(Date.now());
      this.presenceMap.set(id, this.buildPresence(id, snapshot.val()));
      this.emitPresence();
    });

    onChildRemoved(presenceRef, (snapshot) => {
      const id = snapshot.key ?? snapshot.ref.key ?? String(Date.now());
      this.presenceMap.delete(id);
      this.emitPresence();
    });
  }

  observeMessages(): Observable<ChatMessage[]> {
    return this.messagesSubject.asObservable();
  }

  observePresence(): Observable<ChatPresence[]> {
    return this.presenceSubject.asObservable();
  }

  async publishMessage(user: AuthUser, text: string): Promise<void> {
    const trimmedText = text.trim();
    if (trimmedText === '') {
      return;
    }

    const messagesRef = ref(this.database, this.messagesPath);
    await push(messagesRef, {
      text: trimmedText,
      senderId: user.id_usuario,
      senderFirstName: user.nombre,
      senderLastName: user.apellido,
      senderDisplayName: `${user.nombre} ${user.apellido}`.trim(),
      senderPhotoUrl: user.foto_perfil_url ?? null,
      senderRole: user.id_rol,
      createdAtMs: Date.now()
    });
  }

  async setPresence(user: AuthUser, typing: boolean): Promise<void> {
    const presenceRef = ref(this.database, `${this.presencePath}/${this.getPresenceId(user)}`);

    await set(presenceRef, {
      userId: user.id_usuario,
      firstName: user.nombre,
      lastName: user.apellido,
      displayName: `${user.nombre} ${user.apellido}`.trim(),
      photoUrl: user.foto_perfil_url ?? null,
      role: user.id_rol,
      online: true,
      typing,
      lastSeenMs: Date.now()
    });

    await onDisconnect(presenceRef).update({
      online: false,
      typing: false,
      lastSeenMs: Date.now()
    });
  }

  async setTypingState(user: AuthUser, typing: boolean): Promise<void> {
    const presenceRef = ref(this.database, `${this.presencePath}/${this.getPresenceId(user)}`);

    await update(presenceRef, {
      typing,
      lastSeenMs: Date.now()
    });
  }

  async markOffline(user: AuthUser): Promise<void> {
    const presenceRef = ref(this.database, `${this.presencePath}/${this.getPresenceId(user)}`);

    await update(presenceRef, {
      online: false,
      typing: false,
      lastSeenMs: Date.now()
    });
  }

  private getPresenceId(user: AuthUser): string {
    return `usuario_${user.id_usuario}`;
  }

  private buildMessage(id: string, rawValue: unknown): ChatMessage {
    const data = (rawValue as Partial<ChatMessage> | null) ?? {};

    return {
      id,
      text: String(data.text ?? ''),
      senderId: Number(data.senderId ?? 0),
      senderFirstName: String(data.senderFirstName ?? ''),
      senderLastName: String(data.senderLastName ?? ''),
      senderDisplayName: String(data.senderDisplayName ?? ''),
      senderPhotoUrl: data.senderPhotoUrl ?? null,
      senderRole: Number(data.senderRole ?? 0),
      createdAtMs: Number(data.createdAtMs ?? Date.now())
    };
  }

  private buildPresence(id: string, rawValue: unknown): ChatPresence {
    const data = (rawValue as Partial<ChatPresence> | null) ?? {};

    return {
      id,
      userId: Number(data.userId ?? 0),
      firstName: String(data.firstName ?? ''),
      lastName: String(data.lastName ?? ''),
      displayName: String(data.displayName ?? ''),
      photoUrl: data.photoUrl ?? null,
      role: Number(data.role ?? 0),
      online: Boolean(data.online),
      typing: Boolean(data.typing),
      lastSeenMs: Number(data.lastSeenMs ?? 0)
    };
  }

  private emitMessages(): void {
    const messages = Array.from(this.messagesMap.values()).sort((a, b) => a.createdAtMs - b.createdAtMs);
    this.messagesSubject.next(messages);
  }

  private emitPresence(): void {
    const visibles = Array.from(this.presenceMap.values())
      .filter((presence) => presence.online || Date.now() - presence.lastSeenMs < 90000)
      .sort((a, b) => b.lastSeenMs - a.lastSeenMs);

    this.presenceSubject.next(visibles);
  }
}
