import http from '@/api/http';

interface Data {
    username: string;
    password: string;
}

export default ({ username, password }: Data): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.put('/api/client/account/username', {
            username: username,
            password: password,
        })
            .then(() => {
                reject(new Error('Username change is disabled.'));
            })
            .catch((error) => {
                if (error.response?.status === 403) {
                    reject(new Error(error.response.data.error || 'Username change is disabled.'));
                } else {
                    reject(error);
                }
            });
    });
};
