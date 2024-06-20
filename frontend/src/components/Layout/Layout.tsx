'use client';

import { Layout as AntdLayout, ConfigProvider as AntdConfigProvider, Row, Col } from 'antd';
import React from 'react';
import en from 'antd/locale/en_US';
import dayjs from 'dayjs';
import 'dayjs/locale/it';
import 'dayjs/locale/de';
import 'dayjs/locale/en-gb';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import tz from 'dayjs/plugin/timezone';
import { getClientSideClient } from '../../lib/clientSideClient';
import { ApolloNextAppProvider } from '@apollo/experimental-nextjs-app-support/ssr';

dayjs.extend(localizedFormat)
dayjs.extend(tz);

export default function Layout({
    children
}: {
    children: React.ReactNode
}) {
    dayjs.locale('en-gb');
    dayjs.tz.setDefault('Europe/Rome');

    return (
        <AntdConfigProvider
            locale={en}
            theme={{ token: { fontSize: 16 } }}
        >
            <ApolloNextAppProvider makeClient={() => getClientSideClient("en")}>
                <AntdLayout style={{ padding: 16, minHeight: "100vh" }}>
                    {children}
                </AntdLayout>
            </ApolloNextAppProvider>
        </AntdConfigProvider>
    )
}