import { Injectable, inject } from '@angular/core';
import { Auth } from '@angular/fire/auth';
import { AuthUser } from '../models/auth.model';
import { BehaviorSubject, Observable, Subject } from 'rxjs';
import { filter } from 'rxjs/operators';
import { signInAnonymously } from 'firebase/auth';
import {
  getDatabase, onChildAdded, onChildRemoved, onValue,
  push, ref, set, update, onDisconnect
} from 'firebase/database';

export interface ForoEvent {
  id: string;
  type: 'tema_creado' | 'respuesta_creada' | 'respuesta_editada' | 'respuesta_eliminada'
    | 'tema_cerrado' | 'tema_abierto' | 'tema_fijado' | 'tema_desfijado' | 'tema_eliminado';
  id_tema: number;
  id_respuesta?: number;
  id_usuario: number;
  usuario_nombre: string;
  timestamp: number;
}

export interface ForoPresence {
  id: string;
  userId: number;
  displayName: string;
  photoUrl: string | null;
  online: boolean;
  lastSeenMs: number;
}

@Injectable({ providedIn: 'root' })
export class ForoRealtimeService {
  private readonly firebaseAuth = inject(Auth);
  private readonly database = getDatabase();
  private readonly eventsPath = 'foro/events';
  private sessionPromise: Promise<void> | null = null;

  private readonly eventsSubject = new Subject<ForoEvent>();
  private readonly presenceSubject = new BehaviorSubject<ForoPresence[]>([]);
  private readonly presenceMap = new Map<string, ForoPresence>();

  private eventsListenerAttached = false;
  private currentTopicPresencePath: string | null = null;

  // ------------------------------------------------------------------
  // AUTH
  // ------------------------------------------------------------------

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

  // ------------------------------------------------------------------
  // EVENTS (new topics, responses, state changes)
  // ------------------------------------------------------------------

  startListeningEvents(): void {
    if (this.eventsListenerAttached) return;
    this.eventsListenerAttached = true;

    const eventsRef = ref(this.database, this.eventsPath);
    onChildAdded(eventsRef, (snapshot) => {
      const evento = this.buildEvent(snapshot.key ?? '', snapshot.val());
      this.eventsSubject.next(evento);
    });

    onChildRemoved(eventsRef, (snapshot) => {
      const evento = this.buildEvent(snapshot.key ?? '', snapshot.val());
      this.eventsSubject.next(evento);
    });
  }

  /**
   * Emite solo eventos de un tipo específico.
   */
  observeEventsByType(type: ForoEvent['type']): Observable<ForoEvent> {
    return this.eventsSubject.asObservable().pipe(
      filter((e: ForoEvent) => e.type === type)
    );
  }

  /**
   * Emite eventos relacionados a un tema específico.
   */
  observeEventsByTema(idTema: number): Observable<ForoEvent> {
    return this.eventsSubject.asObservable().pipe(
      filter((e: ForoEvent) => e.id_tema === idTema)
    );
  }

  /**
   * Emite cualquier evento (para debugging o uso general).
   */
  observeAllEvents(): Observable<ForoEvent> {
    return this.eventsSubject.asObservable();
  }

  async publishEvent(
    type: ForoEvent['type'],
    idTema: number,
    user: AuthUser,
    extra?: { id_respuesta?: number }
  ): Promise<void> {
    const eventsRef = ref(this.database, this.eventsPath);
    await push(eventsRef, {
      type,
      id_tema: idTema,
      id_respuesta: extra?.id_respuesta ?? null,
      id_usuario: user.id_usuario,
      usuario_nombre: `${user.nombre} ${user.apellido}`.trim(),
      timestamp: Date.now()
    });
  }

  // ------------------------------------------------------------------
  // PRESENCE (who's viewing a topic)
  // ------------------------------------------------------------------

  startTopicPresence(user: AuthUser, idTema: number): void {
    this.currentTopicPresencePath = `foro/presence/tema_${idTema}`;
    const presenceRef = ref(this.database, `${this.currentTopicPresencePath}/usuario_${user.id_usuario}`);

    set(presenceRef, {
      userId: user.id_usuario,
      displayName: `${user.nombre} ${user.apellido}`.trim(),
      photoUrl: user.foto_perfil_url ?? null,
      online: true,
      lastSeenMs: Date.now()
    });

    onDisconnect(presenceRef).update({
      online: false,
      lastSeenMs: Date.now()
    });

    const topicPresenceRef = ref(this.database, this.currentTopicPresencePath);
    onValue(topicPresenceRef, (snapshot) => {
      const data = snapshot.val() as Record<string, unknown> | null;
      this.presenceMap.clear();
      if (data) {
        Object.entries(data).forEach(([key, value]) => {
          this.presenceMap.set(key, this.buildPresence(key, value));
        });
      }
      this.emitPresence();
    });
  }

  observePresence(): Observable<ForoPresence[]> {
    return this.presenceSubject.asObservable();
  }

  async markOffline(user: AuthUser): Promise<void> {
    if (!this.currentTopicPresencePath) return;
    const presenceRef = ref(this.database, `${this.currentTopicPresencePath}/usuario_${user.id_usuario}`);
    await update(presenceRef, {
      online: false,
      lastSeenMs: Date.now()
    });
  }

  // ------------------------------------------------------------------
  // PRIVATE HELPERS
  // ------------------------------------------------------------------

  private buildEvent(id: string, raw: unknown): ForoEvent {
    const data = (raw as Partial<ForoEvent> | null) ?? {};
    return {
      id,
      type: (data.type ?? 'tema_creado') as ForoEvent['type'],
      id_tema: Number(data.id_tema ?? 0),
      id_respuesta: data.id_respuesta != null ? Number(data.id_respuesta) : undefined,
      id_usuario: Number(data.id_usuario ?? 0),
      usuario_nombre: String(data.usuario_nombre ?? ''),
      timestamp: Number(data.timestamp ?? Date.now())
    };
  }

  private buildPresence(id: string, raw: unknown): ForoPresence {
    const data = (raw as Partial<ForoPresence> | null) ?? {};
    return {
      id,
      userId: Number(data.userId ?? 0),
      displayName: String(data.displayName ?? ''),
      photoUrl: data.photoUrl ?? null,
      online: Boolean(data.online),
      lastSeenMs: Number(data.lastSeenMs ?? 0)
    };
  }

  private emitPresence(): void {
    const visibles = Array.from(this.presenceMap.values())
      .filter((p) => p.online || Date.now() - p.lastSeenMs < 90000)
      .sort((a, b) => b.lastSeenMs - a.lastSeenMs);
    this.presenceSubject.next(visibles);
  }
}
