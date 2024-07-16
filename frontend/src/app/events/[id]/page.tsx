import type { Metadata } from 'next'
import { Card, Col, Row } from 'antd';
import Layout from '../../../components/Layout/Layout';
import { getClient } from '../../../lib/client';
import { EventInterface } from '../../../types/DataModelTypes/EventInterface';
import pageQuery from './_queries/pageQuery';
import Header from './_components/Header/Header';
import Program from './_components/Program/Program';

export async function generateMetadata(): Promise<Metadata> {
    return {
        title: `Event | ${process.env.NEXT_PUBLIC_APP_NAME}`
    }
}
export default async function Event({
    params
}: {
    params: { id: string }
}) {

    const { data } = await getClient().query<{ node: EventInterface }>({
        query: pageQuery,
        context: {
            fetchOptions: {
                next: { revalidate: 60 }
            },
        },
        variables: {
            id: decodeURIComponent(params.id)
        }
    });

    return (
        <Layout>
            <Row gutter={16} justify="center">
                <Col xs={24} sm={20} md={16} lg={14} xl={10} xxl={8}>
                    <Header event={data.node} />
                    <Card title="Program">
                        <Program
                            program={data.node.program} 
                        />
                    </Card>
                </Col>
            </Row>
        </Layout>
    );
}
