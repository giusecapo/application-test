'use client';

import { Typography } from "antd";
import dayjs from "dayjs";
import { EventInterface } from "../../../../../types/DataModelTypes/EventInterface";

export default function Header({
    event
}: {
    event: EventInterface
}) {
    return (
        <>
            <Typography.Title>
                <Typography.Text type="secondary">
                    {dayjs(event.date ?? "").format('L')}
                </Typography.Text>
                <br />
                {event.name}
            </Typography.Title>
        </>
    );
}