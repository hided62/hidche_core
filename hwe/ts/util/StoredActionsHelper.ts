import type { TurnObj } from '@/defs';
import { ref } from 'vue';


export class StoredActionsHelper {
    public readonly recentActions = ref<TurnObj[]>([]);
    public readonly storedActions = ref(new Map<string, [number[], TurnObj][]>());
    public readonly recentActionsKey: string;
    public readonly storedActionsKey: string;

    constructor(protected serverNick: string, protected type: 'general' | 'nation', protected mapName: string, protected unitSet: string, protected maxRecent = 10) {
        this.recentActionsKey = `${serverNick}_${mapName}_${unitSet}_${type}RecentActions`;
        this.storedActionsKey = `${serverNick}_${mapName}_${unitSet}_${type}StoredActions`;
        this.loadRecentActions();
        this.loadStoredActions();
    }

    loadRecentActions() {
        this.recentActions.value = JSON.parse(localStorage.getItem(this.recentActionsKey) ?? '[]');
    }

    pushRecentActions(action: TurnObj) {
        this.recentActions.value.unshift(action);
        if (this.recentActions.value.length > this.maxRecent) {
            this.recentActions.value.pop();
        }
        this.saveRecentActions();
    }

    saveRecentActions() {
        localStorage.setItem(this.recentActionsKey, JSON.stringify(this.recentActions.value));
    }

    loadStoredActions() {
        const rawValue: [string, [number[], TurnObj][]][] = JSON.parse(
            localStorage.getItem(this.storedActionsKey) ?? '[]'
        );
        this.storedActions.value = new Map(rawValue);
    }

    setStoredActions(actionKey: string, actions: [number[], TurnObj][]) {
        this.storedActions.value.set(actionKey, actions);
        console.log(this.storedActions.value);
        this.saveStoredActions();
    }

    deleteStoredActions(actionKey: string) {
        if (this.storedActions.value.delete(actionKey)) {
            this.saveStoredActions();
        }
    }

    saveStoredActions() {
        localStorage.setItem(
            this.storedActionsKey,
            JSON.stringify(Array.from(this.storedActions.value.entries()))
        );
    }

}