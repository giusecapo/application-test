import { SpeechInterface } from "./SpeechInterface"

export interface EventInterface {
    id: string
    key?: string
    name?: string
    date?: string
    participants?: string[]
    program?: SpeechInterface[]
}