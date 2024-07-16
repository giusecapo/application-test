import { List, Typography } from "antd";
import { SpeechInterface } from "../../../../../../types/DataModelTypes/SpeechInterface";
import Utility from "../../../../../../lib/Utility";

const { Text } = Typography

export function ProgramItem(speech: SpeechInterface){
    return (
        <List.Item>
            <List.Item.Meta
                title={speech.speaker}
                description={speech.topic}
            />
            <Text>{Utility.convertMinutesToTimeString(speech.startTime)} - {Utility.convertMinutesToTimeString(speech.endTime)}</Text>
        </List.Item>
    )
}