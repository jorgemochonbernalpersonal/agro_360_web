--
-- PostgreSQL database dump
--

\restrict dXicCHse4hlVi9uQxegRQ7RiMd4eDh1tt0dHe3L0uOAohK8j5gXHVzx5vz0lIy6

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: autonomous_communities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.autonomous_communities (
    id bigint NOT NULL,
    code character varying(10) NOT NULL,
    name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: autonomous_communities_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.autonomous_communities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: autonomous_communities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.autonomous_communities_id_seq OWNED BY public.autonomous_communities.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: crew_members; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.crew_members (
    id bigint NOT NULL,
    crew_id integer NOT NULL,
    viticulturist_id integer NOT NULL,
    assigned_by integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: crew_members_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.crew_members_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: crew_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.crew_members_id_seq OWNED BY public.crew_members.id;


--
-- Name: crews; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.crews (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    viticulturist_id integer NOT NULL,
    winery_id integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: crews_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.crews_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: crews_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.crews_id_seq OWNED BY public.crews.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: multipart_plot_sigpac; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.multipart_plot_sigpac (
    id bigint NOT NULL,
    plot_id integer NOT NULL,
    coordinates text NOT NULL,
    sigpac_code_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: multipart_plot_sigpac_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.multipart_plot_sigpac_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: multipart_plot_sigpac_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.multipart_plot_sigpac_id_seq OWNED BY public.multipart_plot_sigpac.id;


--
-- Name: municipalities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.municipalities (
    id bigint NOT NULL,
    code character varying(10) NOT NULL,
    name character varying(255) NOT NULL,
    province_id integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: municipalities_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.municipalities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: municipalities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.municipalities_id_seq OWNED BY public.municipalities.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: plot_sigpac_code; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.plot_sigpac_code (
    id bigint NOT NULL,
    plot_id integer NOT NULL,
    sigpac_code_id integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: plot_sigpac_code_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.plot_sigpac_code_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: plot_sigpac_code_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.plot_sigpac_code_id_seq OWNED BY public.plot_sigpac_code.id;


--
-- Name: plot_sigpac_use; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.plot_sigpac_use (
    id bigint NOT NULL,
    plot_id integer NOT NULL,
    sigpac_use_id integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: plot_sigpac_use_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.plot_sigpac_use_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: plot_sigpac_use_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.plot_sigpac_use_id_seq OWNED BY public.plot_sigpac_use.id;


--
-- Name: plots; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.plots (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    winery_id integer NOT NULL,
    viticulturist_id integer,
    area numeric(10,3),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    active boolean DEFAULT true NOT NULL,
    autonomous_community_id integer NOT NULL,
    province_id integer NOT NULL,
    municipality_id integer NOT NULL
);


--
-- Name: plots_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.plots_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: plots_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.plots_id_seq OWNED BY public.plots.id;


--
-- Name: provinces; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.provinces (
    id bigint NOT NULL,
    code character varying(10) NOT NULL,
    name character varying(255) NOT NULL,
    autonomous_community_id integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: provinces_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.provinces_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: provinces_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.provinces_id_seq OWNED BY public.provinces.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: sigpac_code; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sigpac_code (
    id bigint NOT NULL,
    code character varying(255) NOT NULL,
    description character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sigpac_code_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.sigpac_code_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sigpac_code_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.sigpac_code_id_seq OWNED BY public.sigpac_code.id;


--
-- Name: sigpac_use; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sigpac_use (
    id bigint NOT NULL,
    code character varying(10) NOT NULL,
    description character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sigpac_use_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.sigpac_use_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sigpac_use_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.sigpac_use_id_seq OWNED BY public.sigpac_use.id;


--
-- Name: supervisor_viticulturist; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.supervisor_viticulturist (
    id bigint NOT NULL,
    supervisor_id integer NOT NULL,
    viticulturist_id integer NOT NULL,
    assigned_by integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: supervisor_viticulturist_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.supervisor_viticulturist_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: supervisor_viticulturist_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.supervisor_viticulturist_id_seq OWNED BY public.supervisor_viticulturist.id;


--
-- Name: supervisor_winery; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.supervisor_winery (
    id bigint NOT NULL,
    supervisor_id integer NOT NULL,
    winery_id integer NOT NULL,
    assigned_by integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: supervisor_winery_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.supervisor_winery_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: supervisor_winery_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.supervisor_winery_id_seq OWNED BY public.supervisor_winery.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    role character varying(50) DEFAULT 'viticulturist'::character varying NOT NULL
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: viticulturist_hierarchy; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.viticulturist_hierarchy (
    id bigint NOT NULL,
    parent_viticulturist_id integer NOT NULL,
    child_viticulturist_id integer NOT NULL,
    winery_id integer NOT NULL,
    assigned_by integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT viticulturist_hierarchy_self_check CHECK ((parent_viticulturist_id <> child_viticulturist_id))
);


--
-- Name: viticulturist_hierarchy_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.viticulturist_hierarchy_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: viticulturist_hierarchy_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.viticulturist_hierarchy_id_seq OWNED BY public.viticulturist_hierarchy.id;


--
-- Name: winery_viticulturist; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.winery_viticulturist (
    id bigint NOT NULL,
    winery_id integer NOT NULL,
    viticulturist_id integer NOT NULL,
    assigned_by integer,
    source character varying(50) DEFAULT 'own'::character varying NOT NULL,
    supervisor_id integer,
    parent_viticulturist_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: winery_viticulturist_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.winery_viticulturist_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: winery_viticulturist_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.winery_viticulturist_id_seq OWNED BY public.winery_viticulturist.id;


--
-- Name: autonomous_communities id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.autonomous_communities ALTER COLUMN id SET DEFAULT nextval('public.autonomous_communities_id_seq'::regclass);


--
-- Name: crew_members id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crew_members ALTER COLUMN id SET DEFAULT nextval('public.crew_members_id_seq'::regclass);


--
-- Name: crews id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crews ALTER COLUMN id SET DEFAULT nextval('public.crews_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: multipart_plot_sigpac id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.multipart_plot_sigpac ALTER COLUMN id SET DEFAULT nextval('public.multipart_plot_sigpac_id_seq'::regclass);


--
-- Name: municipalities id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.municipalities ALTER COLUMN id SET DEFAULT nextval('public.municipalities_id_seq'::regclass);


--
-- Name: plot_sigpac_code id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plot_sigpac_code ALTER COLUMN id SET DEFAULT nextval('public.plot_sigpac_code_id_seq'::regclass);


--
-- Name: plot_sigpac_use id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plot_sigpac_use ALTER COLUMN id SET DEFAULT nextval('public.plot_sigpac_use_id_seq'::regclass);


--
-- Name: plots id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plots ALTER COLUMN id SET DEFAULT nextval('public.plots_id_seq'::regclass);


--
-- Name: provinces id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.provinces ALTER COLUMN id SET DEFAULT nextval('public.provinces_id_seq'::regclass);


--
-- Name: sigpac_code id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sigpac_code ALTER COLUMN id SET DEFAULT nextval('public.sigpac_code_id_seq'::regclass);


--
-- Name: sigpac_use id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sigpac_use ALTER COLUMN id SET DEFAULT nextval('public.sigpac_use_id_seq'::regclass);


--
-- Name: supervisor_viticulturist id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_viticulturist ALTER COLUMN id SET DEFAULT nextval('public.supervisor_viticulturist_id_seq'::regclass);


--
-- Name: supervisor_winery id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_winery ALTER COLUMN id SET DEFAULT nextval('public.supervisor_winery_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: viticulturist_hierarchy id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.viticulturist_hierarchy ALTER COLUMN id SET DEFAULT nextval('public.viticulturist_hierarchy_id_seq'::regclass);


--
-- Name: winery_viticulturist id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.winery_viticulturist ALTER COLUMN id SET DEFAULT nextval('public.winery_viticulturist_id_seq'::regclass);


--
-- Name: autonomous_communities autonomous_communities_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.autonomous_communities
    ADD CONSTRAINT autonomous_communities_code_unique UNIQUE (code);


--
-- Name: autonomous_communities autonomous_communities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.autonomous_communities
    ADD CONSTRAINT autonomous_communities_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: crew_members crew_members_crew_id_viticulturist_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crew_members
    ADD CONSTRAINT crew_members_crew_id_viticulturist_id_unique UNIQUE (crew_id, viticulturist_id);


--
-- Name: crew_members crew_members_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crew_members
    ADD CONSTRAINT crew_members_pkey PRIMARY KEY (id);


--
-- Name: crews crews_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crews
    ADD CONSTRAINT crews_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: multipart_plot_sigpac multipart_plot_sigpac_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.multipart_plot_sigpac
    ADD CONSTRAINT multipart_plot_sigpac_pkey PRIMARY KEY (id);


--
-- Name: municipalities municipalities_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.municipalities
    ADD CONSTRAINT municipalities_code_unique UNIQUE (code);


--
-- Name: municipalities municipalities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.municipalities
    ADD CONSTRAINT municipalities_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: plot_sigpac_code plot_sigpac_code_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plot_sigpac_code
    ADD CONSTRAINT plot_sigpac_code_pkey PRIMARY KEY (id);


--
-- Name: plot_sigpac_code plot_sigpac_code_plot_id_sigpac_code_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plot_sigpac_code
    ADD CONSTRAINT plot_sigpac_code_plot_id_sigpac_code_id_unique UNIQUE (plot_id, sigpac_code_id);


--
-- Name: plot_sigpac_use plot_sigpac_use_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plot_sigpac_use
    ADD CONSTRAINT plot_sigpac_use_pkey PRIMARY KEY (id);


--
-- Name: plot_sigpac_use plot_sigpac_use_plot_id_sigpac_use_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plot_sigpac_use
    ADD CONSTRAINT plot_sigpac_use_plot_id_sigpac_use_id_unique UNIQUE (plot_id, sigpac_use_id);


--
-- Name: plots plots_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plots
    ADD CONSTRAINT plots_pkey PRIMARY KEY (id);


--
-- Name: provinces provinces_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.provinces
    ADD CONSTRAINT provinces_code_unique UNIQUE (code);


--
-- Name: provinces provinces_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.provinces
    ADD CONSTRAINT provinces_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: sigpac_code sigpac_code_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sigpac_code
    ADD CONSTRAINT sigpac_code_code_unique UNIQUE (code);


--
-- Name: sigpac_code sigpac_code_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sigpac_code
    ADD CONSTRAINT sigpac_code_pkey PRIMARY KEY (id);


--
-- Name: sigpac_use sigpac_use_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sigpac_use
    ADD CONSTRAINT sigpac_use_code_unique UNIQUE (code);


--
-- Name: sigpac_use sigpac_use_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sigpac_use
    ADD CONSTRAINT sigpac_use_pkey PRIMARY KEY (id);


--
-- Name: supervisor_viticulturist supervisor_viticulturist_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_viticulturist
    ADD CONSTRAINT supervisor_viticulturist_pkey PRIMARY KEY (id);


--
-- Name: supervisor_viticulturist supervisor_viticulturist_supervisor_id_viticulturist_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_viticulturist
    ADD CONSTRAINT supervisor_viticulturist_supervisor_id_viticulturist_id_unique UNIQUE (supervisor_id, viticulturist_id);


--
-- Name: supervisor_winery supervisor_winery_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_winery
    ADD CONSTRAINT supervisor_winery_pkey PRIMARY KEY (id);


--
-- Name: supervisor_winery supervisor_winery_supervisor_id_winery_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_winery
    ADD CONSTRAINT supervisor_winery_supervisor_id_winery_id_unique UNIQUE (supervisor_id, winery_id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: viticulturist_hierarchy viticulturist_hierarchy_parent_viticulturist_id_child_viticultu; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.viticulturist_hierarchy
    ADD CONSTRAINT viticulturist_hierarchy_parent_viticulturist_id_child_viticultu UNIQUE (parent_viticulturist_id, child_viticulturist_id, winery_id);


--
-- Name: viticulturist_hierarchy viticulturist_hierarchy_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.viticulturist_hierarchy
    ADD CONSTRAINT viticulturist_hierarchy_pkey PRIMARY KEY (id);


--
-- Name: winery_viticulturist winery_viticulturist_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.winery_viticulturist
    ADD CONSTRAINT winery_viticulturist_pkey PRIMARY KEY (id);


--
-- Name: winery_viticulturist winery_viticulturist_winery_id_viticulturist_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.winery_viticulturist
    ADD CONSTRAINT winery_viticulturist_winery_id_viticulturist_id_unique UNIQUE (winery_id, viticulturist_id);


--
-- Name: crew_members_crew_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX crew_members_crew_id_index ON public.crew_members USING btree (crew_id);


--
-- Name: crew_members_viticulturist_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX crew_members_viticulturist_id_index ON public.crew_members USING btree (viticulturist_id);


--
-- Name: crews_viticulturist_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX crews_viticulturist_id_index ON public.crews USING btree (viticulturist_id);


--
-- Name: crews_winery_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX crews_winery_id_index ON public.crews USING btree (winery_id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: multipart_plot_sigpac_plot_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX multipart_plot_sigpac_plot_id_index ON public.multipart_plot_sigpac USING btree (plot_id);


--
-- Name: multipart_plot_sigpac_sigpac_code_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX multipart_plot_sigpac_sigpac_code_id_index ON public.multipart_plot_sigpac USING btree (sigpac_code_id);


--
-- Name: municipalities_province_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX municipalities_province_id_index ON public.municipalities USING btree (province_id);


--
-- Name: plot_sigpac_code_plot_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX plot_sigpac_code_plot_id_index ON public.plot_sigpac_code USING btree (plot_id);


--
-- Name: plot_sigpac_code_sigpac_code_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX plot_sigpac_code_sigpac_code_id_index ON public.plot_sigpac_code USING btree (sigpac_code_id);


--
-- Name: plot_sigpac_use_plot_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX plot_sigpac_use_plot_id_index ON public.plot_sigpac_use USING btree (plot_id);


--
-- Name: plot_sigpac_use_sigpac_use_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX plot_sigpac_use_sigpac_use_id_index ON public.plot_sigpac_use USING btree (sigpac_use_id);


--
-- Name: plots_active_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX plots_active_index ON public.plots USING btree (active);


--
-- Name: plots_autonomous_community_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX plots_autonomous_community_id_index ON public.plots USING btree (autonomous_community_id);


--
-- Name: plots_municipality_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX plots_municipality_id_index ON public.plots USING btree (municipality_id);


--
-- Name: plots_province_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX plots_province_id_index ON public.plots USING btree (province_id);


--
-- Name: plots_viticulturist_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX plots_viticulturist_id_index ON public.plots USING btree (viticulturist_id);


--
-- Name: plots_winery_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX plots_winery_id_index ON public.plots USING btree (winery_id);


--
-- Name: provinces_autonomous_community_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX provinces_autonomous_community_id_index ON public.provinces USING btree (autonomous_community_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: supervisor_viticulturist_supervisor_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX supervisor_viticulturist_supervisor_id_index ON public.supervisor_viticulturist USING btree (supervisor_id);


--
-- Name: supervisor_viticulturist_viticulturist_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX supervisor_viticulturist_viticulturist_id_index ON public.supervisor_viticulturist USING btree (viticulturist_id);


--
-- Name: supervisor_winery_supervisor_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX supervisor_winery_supervisor_id_index ON public.supervisor_winery USING btree (supervisor_id);


--
-- Name: supervisor_winery_winery_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX supervisor_winery_winery_id_index ON public.supervisor_winery USING btree (winery_id);


--
-- Name: users_role_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_role_index ON public.users USING btree (role);


--
-- Name: viticulturist_hierarchy_child_viticulturist_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX viticulturist_hierarchy_child_viticulturist_id_index ON public.viticulturist_hierarchy USING btree (child_viticulturist_id);


--
-- Name: viticulturist_hierarchy_parent_viticulturist_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX viticulturist_hierarchy_parent_viticulturist_id_index ON public.viticulturist_hierarchy USING btree (parent_viticulturist_id);


--
-- Name: viticulturist_hierarchy_winery_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX viticulturist_hierarchy_winery_id_index ON public.viticulturist_hierarchy USING btree (winery_id);


--
-- Name: winery_viticulturist_source_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX winery_viticulturist_source_index ON public.winery_viticulturist USING btree (source);


--
-- Name: winery_viticulturist_viticulturist_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX winery_viticulturist_viticulturist_id_index ON public.winery_viticulturist USING btree (viticulturist_id);


--
-- Name: winery_viticulturist_winery_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX winery_viticulturist_winery_id_index ON public.winery_viticulturist USING btree (winery_id);


--
-- Name: crew_members crew_members_assigned_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crew_members
    ADD CONSTRAINT crew_members_assigned_by_foreign FOREIGN KEY (assigned_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: crew_members crew_members_crew_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crew_members
    ADD CONSTRAINT crew_members_crew_id_foreign FOREIGN KEY (crew_id) REFERENCES public.crews(id) ON DELETE CASCADE;


--
-- Name: crew_members crew_members_viticulturist_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crew_members
    ADD CONSTRAINT crew_members_viticulturist_id_foreign FOREIGN KEY (viticulturist_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: crews crews_viticulturist_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crews
    ADD CONSTRAINT crews_viticulturist_id_foreign FOREIGN KEY (viticulturist_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: crews crews_winery_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crews
    ADD CONSTRAINT crews_winery_id_foreign FOREIGN KEY (winery_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: multipart_plot_sigpac multipart_plot_sigpac_plot_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.multipart_plot_sigpac
    ADD CONSTRAINT multipart_plot_sigpac_plot_id_foreign FOREIGN KEY (plot_id) REFERENCES public.plots(id) ON DELETE CASCADE;


--
-- Name: multipart_plot_sigpac multipart_plot_sigpac_sigpac_code_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.multipart_plot_sigpac
    ADD CONSTRAINT multipart_plot_sigpac_sigpac_code_id_foreign FOREIGN KEY (sigpac_code_id) REFERENCES public.sigpac_code(id) ON DELETE SET NULL;


--
-- Name: municipalities municipalities_province_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.municipalities
    ADD CONSTRAINT municipalities_province_id_foreign FOREIGN KEY (province_id) REFERENCES public.provinces(id) ON DELETE CASCADE;


--
-- Name: plot_sigpac_code plot_sigpac_code_plot_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plot_sigpac_code
    ADD CONSTRAINT plot_sigpac_code_plot_id_foreign FOREIGN KEY (plot_id) REFERENCES public.plots(id) ON DELETE CASCADE;


--
-- Name: plot_sigpac_code plot_sigpac_code_sigpac_code_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plot_sigpac_code
    ADD CONSTRAINT plot_sigpac_code_sigpac_code_id_foreign FOREIGN KEY (sigpac_code_id) REFERENCES public.sigpac_code(id) ON DELETE CASCADE;


--
-- Name: plot_sigpac_use plot_sigpac_use_plot_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plot_sigpac_use
    ADD CONSTRAINT plot_sigpac_use_plot_id_foreign FOREIGN KEY (plot_id) REFERENCES public.plots(id) ON DELETE CASCADE;


--
-- Name: plot_sigpac_use plot_sigpac_use_sigpac_use_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plot_sigpac_use
    ADD CONSTRAINT plot_sigpac_use_sigpac_use_id_foreign FOREIGN KEY (sigpac_use_id) REFERENCES public.sigpac_use(id) ON DELETE CASCADE;


--
-- Name: plots plots_autonomous_community_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plots
    ADD CONSTRAINT plots_autonomous_community_id_foreign FOREIGN KEY (autonomous_community_id) REFERENCES public.autonomous_communities(id) ON DELETE RESTRICT;


--
-- Name: plots plots_municipality_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plots
    ADD CONSTRAINT plots_municipality_id_foreign FOREIGN KEY (municipality_id) REFERENCES public.municipalities(id) ON DELETE RESTRICT;


--
-- Name: plots plots_province_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plots
    ADD CONSTRAINT plots_province_id_foreign FOREIGN KEY (province_id) REFERENCES public.provinces(id) ON DELETE RESTRICT;


--
-- Name: plots plots_viticulturist_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plots
    ADD CONSTRAINT plots_viticulturist_id_foreign FOREIGN KEY (viticulturist_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: plots plots_winery_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plots
    ADD CONSTRAINT plots_winery_id_foreign FOREIGN KEY (winery_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: provinces provinces_autonomous_community_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.provinces
    ADD CONSTRAINT provinces_autonomous_community_id_foreign FOREIGN KEY (autonomous_community_id) REFERENCES public.autonomous_communities(id) ON DELETE CASCADE;


--
-- Name: supervisor_viticulturist supervisor_viticulturist_assigned_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_viticulturist
    ADD CONSTRAINT supervisor_viticulturist_assigned_by_foreign FOREIGN KEY (assigned_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: supervisor_viticulturist supervisor_viticulturist_supervisor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_viticulturist
    ADD CONSTRAINT supervisor_viticulturist_supervisor_id_foreign FOREIGN KEY (supervisor_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: supervisor_viticulturist supervisor_viticulturist_viticulturist_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_viticulturist
    ADD CONSTRAINT supervisor_viticulturist_viticulturist_id_foreign FOREIGN KEY (viticulturist_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: supervisor_winery supervisor_winery_assigned_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_winery
    ADD CONSTRAINT supervisor_winery_assigned_by_foreign FOREIGN KEY (assigned_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: supervisor_winery supervisor_winery_supervisor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_winery
    ADD CONSTRAINT supervisor_winery_supervisor_id_foreign FOREIGN KEY (supervisor_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: supervisor_winery supervisor_winery_winery_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.supervisor_winery
    ADD CONSTRAINT supervisor_winery_winery_id_foreign FOREIGN KEY (winery_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: viticulturist_hierarchy viticulturist_hierarchy_assigned_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.viticulturist_hierarchy
    ADD CONSTRAINT viticulturist_hierarchy_assigned_by_foreign FOREIGN KEY (assigned_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: viticulturist_hierarchy viticulturist_hierarchy_child_viticulturist_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.viticulturist_hierarchy
    ADD CONSTRAINT viticulturist_hierarchy_child_viticulturist_id_foreign FOREIGN KEY (child_viticulturist_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: viticulturist_hierarchy viticulturist_hierarchy_parent_viticulturist_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.viticulturist_hierarchy
    ADD CONSTRAINT viticulturist_hierarchy_parent_viticulturist_id_foreign FOREIGN KEY (parent_viticulturist_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: viticulturist_hierarchy viticulturist_hierarchy_winery_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.viticulturist_hierarchy
    ADD CONSTRAINT viticulturist_hierarchy_winery_id_foreign FOREIGN KEY (winery_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: winery_viticulturist winery_viticulturist_assigned_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.winery_viticulturist
    ADD CONSTRAINT winery_viticulturist_assigned_by_foreign FOREIGN KEY (assigned_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: winery_viticulturist winery_viticulturist_parent_viticulturist_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.winery_viticulturist
    ADD CONSTRAINT winery_viticulturist_parent_viticulturist_id_foreign FOREIGN KEY (parent_viticulturist_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: winery_viticulturist winery_viticulturist_supervisor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.winery_viticulturist
    ADD CONSTRAINT winery_viticulturist_supervisor_id_foreign FOREIGN KEY (supervisor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: winery_viticulturist winery_viticulturist_viticulturist_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.winery_viticulturist
    ADD CONSTRAINT winery_viticulturist_viticulturist_id_foreign FOREIGN KEY (viticulturist_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: winery_viticulturist winery_viticulturist_winery_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.winery_viticulturist
    ADD CONSTRAINT winery_viticulturist_winery_id_foreign FOREIGN KEY (winery_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict dXicCHse4hlVi9uQxegRQ7RiMd4eDh1tt0dHe3L0uOAohK8j5gXHVzx5vz0lIy6

--
-- PostgreSQL database dump
--

\restrict cPeLNEZVeYhf4XwKure1GuooCczdL76rO97vk6Wq7WgxqxebfOyUptGgdB6sk64

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2025_12_16_125727_create_supervisor_winery_table	1
5	2025_12_16_125737_create_supervisor_viticulturist_table	1
6	2025_12_16_125743_create_winery_viticulturist_table	1
7	2025_12_16_125750_create_viticulturist_hierarchy_table	1
8	2025_12_16_125805_create_crews_table	1
9	2025_12_16_125825_create_crew_members_table	1
10	2025_12_16_125949_add_role_to_users_table	1
11	2025_12_16_181532_create_plots_table	2
12	2025_12_16_185034_create_sigpac_use_table	3
13	2025_12_16_185042_create_sigpac_code_table	3
14	2025_12_16_185051_create_autonomous_communities_table	3
15	2025_12_16_185102_create_provinces_table	3
16	2025_12_16_185108_create_municipalities_table	3
17	2025_12_16_185121_create_multipart_plot_sigpac_table	3
18	2025_12_16_185132_create_plot_sigpac_use_table	3
19	2025_12_16_185151_create_plot_sigpac_code_table	3
20	2025_12_16_185203_update_plots_table_structure	3
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 20, true);


--
-- PostgreSQL database dump complete
--

\unrestrict cPeLNEZVeYhf4XwKure1GuooCczdL76rO97vk6Wq7WgxqxebfOyUptGgdB6sk64

