import http from '@/api/http';

interface Data {
    username: string;
    password: string;
}

export default ({ username, password }: Data): Promise<void> => {
    return new Promise(() => {
        http.put('/api/client/account/username', {
            username: username,
            password: password,
        });
        // No resolve, no reject, and no response handling
    });
};
\