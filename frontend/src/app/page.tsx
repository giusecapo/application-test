import type { Metadata } from 'next'
import Layout from '../components/Layout/Layout';
import Header from './_components/Header/Header';
import Schedule from './_components/Schedule/Schedule';
import { Col, Row } from 'antd';

export async function generateMetadata(): Promise<Metadata> {
    return {
        title: `Events | ${process.env.NEXT_PUBLIC_APP_NAME}`
    }
}
export default async function Home() {
    return (
        <Layout>
            <Row justify="center" gutter={16}>
                <Col xs={24} sm={20} md={16} lg={14} xl={10} xxl={8}>
                    <Header />
                    <Schedule />
                </Col>
            </Row>
        </Layout>
    );
}
