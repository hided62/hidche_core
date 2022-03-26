import type { TurnObj } from '@/defs';
import { ref, watch } from 'vue';


export class StoredActionsHelper {
    public readonly recentActions = ref<TurnObj[]>([]);
    public readonly storedActions = ref(new Map<string, [number[], TurnObj][]>());
    public readonly clipboard = ref<[number[], TurnObj][] | undefined>(undefined);
    public readonly activatedCategory = ref<string>("");
    public readonly isEditMode = ref(false);

    public readonly recentActionsKey: string;
    public readonly storedActionsKey: string;
    public readonly clipboardKey: string;
    public readonly activatedCategoryKey: string;
    public readonly editModeKey: string;

    constructor(protected serverNick: string, protected type: 'general' | 'nation', protected mapName: string, protected unitSet: string, protected maxRecent = 10) {
        const typeKey = `${serverNick}_${mapName}_${unitSet}_${type}`;
        this.recentActionsKey = `${typeKey}RecentActions`;
        this.storedActionsKey = `${typeKey}StoredActions`;
        this.clipboardKey = `${typeKey}Clipboard`;
        this.activatedCategoryKey = `${typeKey}ActivatedCategory`;
        this.editModeKey = `${serverNick}_${type}_isEditMode`;

        this.loadRecentActions();
        this.loadStoredActions();

        const rawClipboard = localStorage.getItem(this.clipboardKey);
        if (rawClipboard !== null) {
            this.clipboard.value = JSON.parse(rawClipboard);
        }
        watch(this.clipboard, (newValue) => {
            localStorage.setItem(this.clipboardKey, JSON.stringify(newValue));
        });

        const rawActivatedCategory = localStorage.getItem(this.activatedCategoryKey);
        if (rawActivatedCategory !== null) {
            this.activatedCategory.value = JSON.parse(rawActivatedCategory);
        }
        watch(this.activatedCategory, (newValue) => {
            localStorage.setItem(this.activatedCategoryKey, JSON.stringify(newValue));
        });

        this.isEditMode.value = localStorage.getItem(this.editModeKey) === '1';
        watch(this.isEditMode, (newValue) => {
            localStorage.setItem(this.editModeKey, newValue ? '1' : '0')
        })
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