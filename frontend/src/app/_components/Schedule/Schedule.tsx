'use client';

import { useQuery } from "@apollo/client";
import { Alert, Card, List, Skeleton } from "antd";
import scheduleQuery from "./queries/scheduleQuery";
import dayjs from "dayjs";
import { SubsetInterface } from "../../../types/DataModelTypes/SubsetInterface";
import { EventInterface } from "../../../types/DataModelTypes/EventInterface";
import Link from "next/link";

function EventsList(data: SubsetInterface<EventInterface>){
    return (
        <Card>
            <List
                dataSource={data.items ?? []}
                renderItem={event => (
                    <List.Item
                        key={event.id}
                        actions={[<Link href={`/events/${encodeURIComponent(event.id)}`}>Detail</Link>]}
                    >
                        
                            <List.Item.Meta
                                title={<>{dayjs(event.date).format('L')} - {event.name}</>}
                                description={event.program?.map(speech => speech.speaker).join(", ")}
                            />
                    </List.Item>
                )}
            />
        </Card>
    )
}

function Loading(){
    return <Skeleton loading active />
}

function Error(){
    return (
        <Alert message="An error occoured during the process. No data found, please retry." type="error" />
    ) 
}

export default function Schedule() {

    // TODO: 
    // Handle loading and error states:
    // - Display a loading spinner or skeleton while the GraphQL query is being executed.
    // - Show an error message if the GraphQL server response contains errors.

    const { loading, data, error } = useQuery<{ eventsSubset: SubsetInterface<EventInterface> }>(
        scheduleQuery,
        {
            fetchPolicy: "network-only",
            nextFetchPolicy: "cache-first",
            variables: {
                limit: 10,
                offset: 0,
                startDate: dayjs().startOf("day").toISOString()
            }
        }
    );

    console.error("EVENTS LIST FETCH ERROR:", error)

    if(loading) return Loading()

        
    if(data){
        return EventsList(data.eventsSubset)
    } else {
        return Error()
    }
}