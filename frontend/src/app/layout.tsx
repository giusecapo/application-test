import React from 'react';
import StyledComponentsRegistry from '../components/Layout/StyledComponentsRegistry';

export default async function RootLayout({
    children,
    params,
}: {
    children: React.ReactNode
    params: { language: string }
}) {
    return (
        <html lang={params.language}>
            <head>
                <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"></meta>
            </head>
            <body style={{ padding: 0, margin: 0 }}>
                <StyledComponentsRegistry>{children}</StyledComponentsRegistry>
            </body>
        </html>
    );
}
