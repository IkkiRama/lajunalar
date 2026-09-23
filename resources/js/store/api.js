import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react';

const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

export const api = createApi({
    reducerPath: 'api',
    baseQuery: fetchBaseQuery({
        baseUrl: '/api/v1',
        credentials: 'same-origin',
        prepareHeaders: (headers) => {
            headers.set('Accept', 'application/json');
            if (csrf) headers.set('X-CSRF-TOKEN', csrf);
            return headers;
        },
    }),
    tagTypes: ['Dashboard', 'History', 'Quiz', 'Attempt', 'AdminQuiz', 'Generation'],
    endpoints: (builder) => ({
        me: builder.query({ query: () => '/me' }),
        dashboard: builder.query({ query: ({ page = 1 } = {}) => `/dashboard?page=${page}`, providesTags: ['Dashboard'] }),
        attemptHistory: builder.query({ query: ({ page = 1 } = {}) => `/history?page=${page}`, providesTags: ['History'] }),
        quizzes: builder.query({ query: ({ page = 1 } = {}) => `/quizzes?page=${page}`, providesTags: ['Quiz'] }),
        quiz: builder.query({ query: (uuid) => `/quizzes/${uuid}`, providesTags: ['Quiz'] }),
        startAttempt: builder.mutation({ query: (quiz) => ({ url: `/quizzes/${quiz}/attempts`, method: 'POST' }), invalidatesTags: ['Dashboard', 'History', 'Quiz'] }),
        attempt: builder.query({ query: (uuid) => `/attempts/${uuid}`, providesTags: ['Attempt'] }),
        saveAnswers: builder.mutation({ query: ({ attempt, answers }) => ({ url: `/attempts/${attempt}/answers/batch`, method: 'POST', body: { answers } }) }),
        finishAttempt: builder.mutation({ query: (uuid) => ({ url: `/attempts/${uuid}/finish`, method: 'POST' }), invalidatesTags: ['Dashboard', 'History', 'Quiz', 'Attempt'] }),
        result: builder.query({ query: (uuid) => `/attempts/${uuid}/result`, providesTags: ['Attempt'] }),
        reportQuestion: builder.mutation({ query: ({ question, ...body }) => ({ url: `/questions/${question}/reports`, method: 'POST', body }) }),
        adminQuizzes: builder.query({ query: () => '/admin/quizzes', providesTags: ['AdminQuiz'] }),
        createAdminQuiz: builder.mutation({
            query: (body) => ({ url: '/admin/quizzes', method: 'POST', body }),
            invalidatesTags: ['AdminQuiz', 'Quiz'],
        }),
        updateAdminQuiz: builder.mutation({
            query: ({ uuid, ...body }) => ({ url: `/admin/quizzes/${uuid}`, method: 'PUT', body }),
            invalidatesTags: ['AdminQuiz', 'Quiz'],
        }),
        deleteAdminQuiz: builder.mutation({
            query: (uuid) => ({ url: `/admin/quizzes/${uuid}`, method: 'DELETE' }),
            invalidatesTags: ['AdminQuiz', 'Quiz'],
        }),
        generationRuns: builder.query({ query: () => '/admin/generations', providesTags: ['Generation'] }),
        generation: builder.query({ query: (uuid) => `/admin/generations/${uuid}`, providesTags: (_result, _error, uuid) => [{ type: 'Generation', id: uuid }] }),
        startGeneration: builder.mutation({
            query: ({ quiz, ...body }) => ({ url: `/admin/quizzes/${quiz}/generations`, method: 'POST', body }),
            invalidatesTags: ['Generation', 'AdminQuiz'],
        }),
        resumeGeneration: builder.mutation({
            query: (uuid) => ({ url: `/admin/generations/${uuid}/resume`, method: 'POST' }),
            invalidatesTags: (_result, _error, uuid) => ['Generation', { type: 'Generation', id: uuid }],
        }),
    }),
});

export const {
    useMeQuery, useDashboardQuery, useAttemptHistoryQuery, useQuizzesQuery, useQuizQuery, useStartAttemptMutation,
    useAttemptQuery, useSaveAnswersMutation, useFinishAttemptMutation, useResultQuery,
    useReportQuestionMutation,
    useAdminQuizzesQuery, useCreateAdminQuizMutation, useUpdateAdminQuizMutation,
    useDeleteAdminQuizMutation, useGenerationRunsQuery, useGenerationQuery,
    useStartGenerationMutation, useResumeGenerationMutation,
} = api;
