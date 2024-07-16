export default class Utility {

    static debounce(debouncedCallback: (...args: any[]) => any, delay: number = 500): (...args: any[]) => void {
        let timer: NodeJS.Timeout;

        return function (...args: any[]) {
            clearTimeout(timer);
            timer = setTimeout(() => debouncedCallback(...args), delay);
        };
    };

    static arrayOfObjsToHashMap<T>(array: T[], keyProp: string): { [key: string]: T } {
        const hashMap: { [key: string]: T } = {};

        array.forEach((item) => {
            hashMap[item[keyProp as keyof T] as string] = item;
        });

        return hashMap;
    }

    static convertMinutesToTimeString(minutes: number): string {
        const h = Math.floor(minutes / 60);
        const m = minutes % 60;
    
        return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}`;
    }
}