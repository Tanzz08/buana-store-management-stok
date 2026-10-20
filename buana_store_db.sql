--
-- PostgreSQL database dump
--

-- Dumped from database version 17.4
-- Dumped by pg_dump version 17.4

-- Started on 2026-10-02 13:04:12

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
-- TOC entry 231 (class 1259 OID 25803)
-- Name: activity_logs; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.activity_logs (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    action character varying(50) NOT NULL,
    ip_address character varying(45) NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.activity_logs OWNER TO buana_store;

--
-- TOC entry 230 (class 1259 OID 25802)
-- Name: activity_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.activity_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.activity_logs_id_seq OWNER TO buana_store;

--
-- TOC entry 5126 (class 0 OID 0)
-- Dependencies: 230
-- Name: activity_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.activity_logs_id_seq OWNED BY public.activity_logs.id;


--
-- TOC entry 223 (class 1259 OID 25757)
-- Name: cache; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO buana_store;

--
-- TOC entry 224 (class 1259 OID 25765)
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO buana_store;

--
-- TOC entry 233 (class 1259 OID 25816)
-- Name: categories; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.categories (
    id bigint NOT NULL,
    name character varying(100) NOT NULL,
    description character varying(500),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.categories OWNER TO buana_store;

--
-- TOC entry 232 (class 1259 OID 25815)
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories_id_seq OWNER TO buana_store;

--
-- TOC entry 5127 (class 0 OID 0)
-- Dependencies: 232
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- TOC entry 229 (class 1259 OID 25791)
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: buana_store
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


ALTER TABLE public.failed_jobs OWNER TO buana_store;

--
-- TOC entry 228 (class 1259 OID 25790)
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO buana_store;

--
-- TOC entry 5128 (class 0 OID 0)
-- Dependencies: 228
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- TOC entry 227 (class 1259 OID 25783)
-- Name: job_batches; Type: TABLE; Schema: public; Owner: buana_store
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


ALTER TABLE public.job_batches OWNER TO buana_store;

--
-- TOC entry 226 (class 1259 OID 25774)
-- Name: jobs; Type: TABLE; Schema: public; Owner: buana_store
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


ALTER TABLE public.jobs OWNER TO buana_store;

--
-- TOC entry 225 (class 1259 OID 25773)
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO buana_store;

--
-- TOC entry 5129 (class 0 OID 0)
-- Dependencies: 225
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- TOC entry 249 (class 1259 OID 25954)
-- Name: low_stock_notifications; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.low_stock_notifications (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    stock_at_creation integer NOT NULL,
    status character varying(255) DEFAULT 'unread'::character varying NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    read_at timestamp(0) without time zone,
    resolved_at timestamp(0) without time zone,
    CONSTRAINT low_stock_notifications_status_check CHECK (((status)::text = ANY ((ARRAY['unread'::character varying, 'read'::character varying, 'resolved'::character varying])::text[])))
);


ALTER TABLE public.low_stock_notifications OWNER TO buana_store;

--
-- TOC entry 248 (class 1259 OID 25953)
-- Name: low_stock_notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.low_stock_notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.low_stock_notifications_id_seq OWNER TO buana_store;

--
-- TOC entry 5130 (class 0 OID 0)
-- Dependencies: 248
-- Name: low_stock_notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.low_stock_notifications_id_seq OWNED BY public.low_stock_notifications.id;


--
-- TOC entry 218 (class 1259 OID 25722)
-- Name: migrations; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO buana_store;

--
-- TOC entry 217 (class 1259 OID 25721)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO buana_store;

--
-- TOC entry 5131 (class 0 OID 0)
-- Dependencies: 217
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 251 (class 1259 OID 25971)
-- Name: notification_reads; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.notification_reads (
    id bigint NOT NULL,
    notification_id bigint NOT NULL,
    user_id bigint NOT NULL,
    read_at timestamp(0) without time zone NOT NULL
);


ALTER TABLE public.notification_reads OWNER TO buana_store;

--
-- TOC entry 250 (class 1259 OID 25970)
-- Name: notification_reads_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.notification_reads_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.notification_reads_id_seq OWNER TO buana_store;

--
-- TOC entry 5132 (class 0 OID 0)
-- Dependencies: 250
-- Name: notification_reads_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.notification_reads_id_seq OWNED BY public.notification_reads.id;


--
-- TOC entry 221 (class 1259 OID 25741)
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO buana_store;

--
-- TOC entry 237 (class 1259 OID 25848)
-- Name: product_change_logs; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.product_change_logs (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    user_id bigint NOT NULL,
    before_data json NOT NULL,
    after_data json NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.product_change_logs OWNER TO buana_store;

--
-- TOC entry 236 (class 1259 OID 25847)
-- Name: product_change_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.product_change_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.product_change_logs_id_seq OWNER TO buana_store;

--
-- TOC entry 5133 (class 0 OID 0)
-- Dependencies: 236
-- Name: product_change_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.product_change_logs_id_seq OWNED BY public.product_change_logs.id;


--
-- TOC entry 235 (class 1259 OID 25827)
-- Name: products; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.products (
    id bigint NOT NULL,
    category_id bigint NOT NULL,
    sku character varying(100) NOT NULL,
    name character varying(255) NOT NULL,
    purchase_price numeric(15,2) NOT NULL,
    selling_price numeric(15,2) NOT NULL,
    unit character varying(50) NOT NULL,
    current_stock integer DEFAULT 0 NOT NULL,
    minimum_stock integer NOT NULL,
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT products_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'inactive'::character varying])::text[])))
);


ALTER TABLE public.products OWNER TO buana_store;

--
-- TOC entry 234 (class 1259 OID 25826)
-- Name: products_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.products_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.products_id_seq OWNER TO buana_store;

--
-- TOC entry 5134 (class 0 OID 0)
-- Dependencies: 234
-- Name: products_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.products_id_seq OWNED BY public.products.id;


--
-- TOC entry 245 (class 1259 OID 25919)
-- Name: sale_items; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.sale_items (
    id bigint NOT NULL,
    sale_id bigint NOT NULL,
    product_id bigint NOT NULL,
    quantity integer NOT NULL,
    unit_price numeric(15,2) NOT NULL,
    subtotal numeric(15,2) NOT NULL
);


ALTER TABLE public.sale_items OWNER TO buana_store;

--
-- TOC entry 244 (class 1259 OID 25918)
-- Name: sale_items_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.sale_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sale_items_id_seq OWNER TO buana_store;

--
-- TOC entry 5135 (class 0 OID 0)
-- Dependencies: 244
-- Name: sale_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.sale_items_id_seq OWNED BY public.sale_items.id;


--
-- TOC entry 243 (class 1259 OID 25904)
-- Name: sales; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.sales (
    id bigint NOT NULL,
    invoice_number character varying(50) NOT NULL,
    user_id bigint NOT NULL,
    total_amount numeric(15,2) NOT NULL,
    sold_at timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.sales OWNER TO buana_store;

--
-- TOC entry 242 (class 1259 OID 25903)
-- Name: sales_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.sales_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sales_id_seq OWNER TO buana_store;

--
-- TOC entry 5136 (class 0 OID 0)
-- Dependencies: 242
-- Name: sales_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.sales_id_seq OWNED BY public.sales.id;


--
-- TOC entry 222 (class 1259 OID 25748)
-- Name: sessions; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO buana_store;

--
-- TOC entry 247 (class 1259 OID 25936)
-- Name: stock_adjustments; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.stock_adjustments (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    user_id bigint NOT NULL,
    stock_before integer NOT NULL,
    stock_after integer NOT NULL,
    difference integer NOT NULL,
    reason character varying(255) NOT NULL,
    adjusted_at timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.stock_adjustments OWNER TO buana_store;

--
-- TOC entry 246 (class 1259 OID 25935)
-- Name: stock_adjustments_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.stock_adjustments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.stock_adjustments_id_seq OWNER TO buana_store;

--
-- TOC entry 5137 (class 0 OID 0)
-- Dependencies: 246
-- Name: stock_adjustments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.stock_adjustments_id_seq OWNED BY public.stock_adjustments.id;


--
-- TOC entry 241 (class 1259 OID 25879)
-- Name: stock_ins; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.stock_ins (
    id bigint NOT NULL,
    reference_number character varying(20) NOT NULL,
    product_id bigint NOT NULL,
    supplier_id bigint NOT NULL,
    user_id bigint NOT NULL,
    quantity integer NOT NULL,
    unit_price numeric(15,2) NOT NULL,
    total_value numeric(15,2) NOT NULL,
    received_at date NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.stock_ins OWNER TO buana_store;

--
-- TOC entry 240 (class 1259 OID 25878)
-- Name: stock_ins_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.stock_ins_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.stock_ins_id_seq OWNER TO buana_store;

--
-- TOC entry 5138 (class 0 OID 0)
-- Dependencies: 240
-- Name: stock_ins_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.stock_ins_id_seq OWNED BY public.stock_ins.id;


--
-- TOC entry 239 (class 1259 OID 25868)
-- Name: suppliers; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.suppliers (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    phone character varying(20),
    email character varying(255),
    address text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.suppliers OWNER TO buana_store;

--
-- TOC entry 238 (class 1259 OID 25867)
-- Name: suppliers_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.suppliers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.suppliers_id_seq OWNER TO buana_store;

--
-- TOC entry 5139 (class 0 OID 0)
-- Dependencies: 238
-- Name: suppliers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.suppliers_id_seq OWNED BY public.suppliers.id;


--
-- TOC entry 220 (class 1259 OID 25729)
-- Name: users; Type: TABLE; Schema: public; Owner: buana_store
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    role character varying(255) NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['admin'::character varying, 'kasir'::character varying, 'gudang'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO buana_store;

--
-- TOC entry 219 (class 1259 OID 25728)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: buana_store
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO buana_store;

--
-- TOC entry 5140 (class 0 OID 0)
-- Dependencies: 219
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: buana_store
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 4838 (class 2604 OID 25806)
-- Name: activity_logs id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.activity_logs ALTER COLUMN id SET DEFAULT nextval('public.activity_logs_id_seq'::regclass);


--
-- TOC entry 4840 (class 2604 OID 25819)
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- TOC entry 4836 (class 2604 OID 25794)
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- TOC entry 4835 (class 2604 OID 25777)
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- TOC entry 4851 (class 2604 OID 25957)
-- Name: low_stock_notifications id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.low_stock_notifications ALTER COLUMN id SET DEFAULT nextval('public.low_stock_notifications_id_seq'::regclass);


--
-- TOC entry 4832 (class 2604 OID 25725)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 4854 (class 2604 OID 25974)
-- Name: notification_reads id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.notification_reads ALTER COLUMN id SET DEFAULT nextval('public.notification_reads_id_seq'::regclass);


--
-- TOC entry 4844 (class 2604 OID 25851)
-- Name: product_change_logs id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.product_change_logs ALTER COLUMN id SET DEFAULT nextval('public.product_change_logs_id_seq'::regclass);


--
-- TOC entry 4841 (class 2604 OID 25830)
-- Name: products id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.products ALTER COLUMN id SET DEFAULT nextval('public.products_id_seq'::regclass);


--
-- TOC entry 4849 (class 2604 OID 25922)
-- Name: sale_items id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.sale_items ALTER COLUMN id SET DEFAULT nextval('public.sale_items_id_seq'::regclass);


--
-- TOC entry 4848 (class 2604 OID 25907)
-- Name: sales id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.sales ALTER COLUMN id SET DEFAULT nextval('public.sales_id_seq'::regclass);


--
-- TOC entry 4850 (class 2604 OID 25939)
-- Name: stock_adjustments id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.stock_adjustments ALTER COLUMN id SET DEFAULT nextval('public.stock_adjustments_id_seq'::regclass);


--
-- TOC entry 4847 (class 2604 OID 25882)
-- Name: stock_ins id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.stock_ins ALTER COLUMN id SET DEFAULT nextval('public.stock_ins_id_seq'::regclass);


--
-- TOC entry 4846 (class 2604 OID 25871)
-- Name: suppliers id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.suppliers ALTER COLUMN id SET DEFAULT nextval('public.suppliers_id_seq'::regclass);


--
-- TOC entry 4833 (class 2604 OID 25732)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 5100 (class 0 OID 25803)
-- Dependencies: 231
-- Data for Name: activity_logs; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.activity_logs (id, user_id, action, ip_address, created_at) FROM stdin;
1	1	login	127.0.0.1	2026-09-09 08:14:06
2	1	logout	127.0.0.1	2026-09-09 08:14:51
3	1	login	127.0.0.1	2026-09-09 09:47:53
4	1	login	127.0.0.1	2026-09-26 06:00:39
5	2	login	127.0.0.1	2026-09-26 06:01:56
6	2	logout	127.0.0.1	2026-09-26 06:30:19
7	1	login	127.0.0.1	2026-09-26 06:30:30
8	1	logout	127.0.0.1	2026-09-26 06:36:41
9	1	logout	127.0.0.1	2026-09-26 06:41:16
10	2	login	127.0.0.1	2026-09-26 06:41:24
11	2	logout	127.0.0.1	2026-09-26 06:41:54
12	2	login	127.0.0.1	2026-09-26 06:46:12
13	2	logout	127.0.0.1	2026-09-26 06:48:15
14	1	login	127.0.0.1	2026-09-26 06:48:45
15	1	logout	127.0.0.1	2026-09-26 08:07:50
16	1	login	127.0.0.1	2026-09-28 07:49:38
17	1	logout	127.0.0.1	2026-09-28 10:25:36
18	1	login	127.0.0.1	2026-09-28 10:29:58
19	1	login	127.0.0.1	2026-10-02 04:40:35
20	1	logout	127.0.0.1	2026-10-02 04:52:26
21	2	login	127.0.0.1	2026-10-02 04:52:52
22	2	logout	127.0.0.1	2026-10-02 04:53:39
\.


--
-- TOC entry 5092 (class 0 OID 25757)
-- Dependencies: 223
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.cache (key, value, expiration) FROM stdin;
buana-store-cache-admin@gmail.com|127.0.0.1:timer	i:1790405370;	1790405370
buana-store-cache-admin@gmail.com|127.0.0.1	i:1;	1790405370
\.


--
-- TOC entry 5093 (class 0 OID 25765)
-- Dependencies: 224
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- TOC entry 5102 (class 0 OID 25816)
-- Dependencies: 233
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.categories (id, name, description, created_at, updated_at) FROM stdin;
1	Kartu Sim	\N	2026-09-26 06:31:06	2026-09-26 06:31:06
2	Kabel Cas	\N	2026-09-26 06:38:07	2026-09-26 06:38:07
3	Aksesoris	\N	2026-10-02 04:43:48	2026-10-02 04:43:48
\.


--
-- TOC entry 5098 (class 0 OID 25791)
-- Dependencies: 229
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- TOC entry 5096 (class 0 OID 25783)
-- Dependencies: 227
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- TOC entry 5095 (class 0 OID 25774)
-- Dependencies: 226
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- TOC entry 5118 (class 0 OID 25954)
-- Dependencies: 249
-- Data for Name: low_stock_notifications; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.low_stock_notifications (id, product_id, stock_at_creation, status, created_at, read_at, resolved_at) FROM stdin;
1	3	1	resolved	2026-09-28 09:06:15	\N	2026-09-28 10:38:51
\.


--
-- TOC entry 5087 (class 0 OID 25722)
-- Dependencies: 218
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2024_01_01_000001_create_activity_logs_table	1
5	2024_01_01_000002_create_categories_table	1
6	2024_01_01_000003_create_products_table	1
7	2024_01_01_000004_create_product_change_logs_table	1
8	2024_01_01_000005_create_suppliers_table	1
9	2024_01_01_000006_create_stock_ins_table	1
10	2024_01_01_000007_create_sales_table	1
11	2024_01_01_000008_create_stock_adjustments_table	1
12	2024_01_01_000009_create_low_stock_notifications_table	1
13	2024_01_01_000010_create_notification_reads_table	1
\.


--
-- TOC entry 5120 (class 0 OID 25971)
-- Dependencies: 251
-- Data for Name: notification_reads; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.notification_reads (id, notification_id, user_id, read_at) FROM stdin;
\.


--
-- TOC entry 5090 (class 0 OID 25741)
-- Dependencies: 221
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- TOC entry 5106 (class 0 OID 25848)
-- Dependencies: 237
-- Data for Name: product_change_logs; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.product_change_logs (id, product_id, user_id, before_data, after_data, created_at) FROM stdin;
1	1	1	{"status":"active"}	{"status":"inactive"}	2026-09-26 08:04:02
2	1	1	{"status":"inactive"}	{"status":"active"}	2026-09-26 08:04:20
3	2	1	{"status":"active"}	{"status":"inactive"}	2026-10-02 04:42:48
4	3	1	{"status":"active"}	{"status":"inactive"}	2026-10-02 04:42:51
\.


--
-- TOC entry 5104 (class 0 OID 25827)
-- Dependencies: 235
-- Data for Name: products; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.products (id, category_id, sku, name, purchase_price, selling_price, unit, current_stock, minimum_stock, status, created_at, updated_at) FROM stdin;
2	2	01233	Vivan USB Type A	25000.00	30000.00	pcs	0	1	inactive	2026-09-26 06:38:52	2026-10-02 04:42:48
3	2	012345	Bintang USB Type A to Type C	10000.00	15000.00	pcs	10	1	inactive	2026-09-26 06:39:42	2026-10-02 04:42:51
1	1	00123	Indosat Kartu Perdana	25000.00	27000.00	pcs	89	1	active	2026-09-26 06:32:29	2026-09-26 08:04:20
4	3	001	Headset samsung	15000.00	20000.00	unit	48	2	active	2026-10-02 04:45:39	2026-10-02 04:51:06
\.


--
-- TOC entry 5114 (class 0 OID 25919)
-- Dependencies: 245
-- Data for Name: sale_items; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.sale_items (id, sale_id, product_id, quantity, unit_price, subtotal) FROM stdin;
1	1	3	1	15000.00	15000.00
2	2	3	48	15000.00	720000.00
3	3	1	10	27000.00	270000.00
4	4	4	1	20000.00	20000.00
5	4	1	1	27000.00	27000.00
\.


--
-- TOC entry 5112 (class 0 OID 25904)
-- Dependencies: 243
-- Data for Name: sales; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.sales (id, invoice_number, user_id, total_amount, sold_at, created_at, updated_at) FROM stdin;
1	INV20260926000001	2	15000.00	2026-09-26 06:47:20	2026-09-26 06:47:20	2026-09-26 06:47:20
2	INV20260928000001	1	720000.00	2026-09-28 09:06:15	2026-09-28 09:06:15	2026-09-28 09:06:15
3	INV20260928000002	1	270000.00	2026-09-28 09:19:58	2026-09-28 09:19:58	2026-09-28 09:19:58
4	INV20261002000001	1	47000.00	2026-10-02 04:49:15	2026-10-02 04:49:15	2026-10-02 04:49:15
\.


--
-- TOC entry 5091 (class 0 OID 25748)
-- Dependencies: 222
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
RLDqb4DRLoMsk5weON1OBhsY4VHHD7EgD0v01JgP	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaG92NVAxNFNBbTdsdVZpUFprYzRaMlNrbnZSVWxRWkZSVUFuVkU2OSI7czoxNDoiX2xhc3RfYWN0aXZpdHkiO2k6MTc5MDkxNjgxOTtzOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9fQ==	1790916820
6YN7E2eSb8xXUC3WgtZQDbasa33niIUHSsH572CK	1	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36	YTo1OntzOjY6Il90b2tlbiI7czo0MDoiR05xSXZmRTV1YmRjakZza3RCS3I1RVIxQlFVS2ZVemNXTUJrNHZmcyI7czoxNDoiX2xhc3RfYWN0aXZpdHkiO2k6MTc5MDYwNTYzMjtzOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo0MDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL3VzZXJzLzMvZWRpdCI7czo1OiJyb3V0ZSI7czoxNjoiYWRtaW4udXNlcnMuZWRpdCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==	1790605632
\.


--
-- TOC entry 5116 (class 0 OID 25936)
-- Dependencies: 247
-- Data for Name: stock_adjustments; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.stock_adjustments (id, product_id, user_id, stock_before, stock_after, difference, reason, adjusted_at, created_at, updated_at) FROM stdin;
1	3	1	1	10	9	ada barang yang lupa di ambil di gudang	2026-09-28 10:38:51	2026-09-28 10:38:51	2026-09-28 10:38:51
2	4	1	49	48	-1	barang rusak 1 unit	2026-10-02 04:51:06	2026-10-02 04:51:06	2026-10-02 04:51:06
\.


--
-- TOC entry 5110 (class 0 OID 25879)
-- Dependencies: 241
-- Data for Name: stock_ins; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.stock_ins (id, reference_number, product_id, supplier_id, user_id, quantity, unit_price, total_value, received_at, created_at, updated_at) FROM stdin;
1	SI20260926JEXTJORSNI	1	1	1	100	25000.00	2500000.00	2026-09-26	2026-09-26 06:34:08	2026-09-26 06:34:08
2	SI202609268T3ITWUS5X	3	2	1	50	25000.00	1250000.00	2026-09-26	2026-09-26 06:40:59	2026-09-26 06:40:59
3	SI20261002GTMKI9YXLM	4	2	1	50	15000.00	750000.00	2026-10-02	2026-10-02 04:48:00	2026-10-02 04:48:00
\.


--
-- TOC entry 5108 (class 0 OID 25868)
-- Dependencies: 239
-- Data for Name: suppliers; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.suppliers (id, name, phone, email, address, created_at, updated_at) FROM stdin;
1	Indosat Pangkep	089123323445	indosatpkp@gmail.com	Labakkang	2026-09-26 06:33:39	2026-09-26 06:33:39
2	Toko Bintang	\N	\N	\N	2026-09-26 06:40:27	2026-09-26 06:40:27
\.


--
-- TOC entry 5089 (class 0 OID 25729)
-- Dependencies: 220
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: buana_store
--

COPY public.users (id, name, email, email_verified_at, password, role, is_active, remember_token, created_at, updated_at) FROM stdin;
3	Gudang Buana Store	gudang@buanastore.com	2026-09-09 05:59:00	$2y$12$JTp.KXOLXkHOBZxFiYJNVey/Gw5190rs835TIx0KX26IfyX8Z.Ma6	gudang	t	fPdDKNAHHZ	2026-09-09 05:59:00	2026-09-09 05:59:00
1	Admin Buana Store	admin@buanastore.com	2026-09-09 05:59:00	$2y$12$JTp.KXOLXkHOBZxFiYJNVey/Gw5190rs835TIx0KX26IfyX8Z.Ma6	admin	t	GHKHWPi1M4cYQxHGxxnrUwQ2KBPoH317qjj2hvJr5n6sDLv0GiF4uepeuVJK	2026-09-09 05:59:00	2026-09-09 05:59:00
2	Kasir Buana Store	kasir@buanastore.com	2026-09-09 05:59:00	$2y$12$JTp.KXOLXkHOBZxFiYJNVey/Gw5190rs835TIx0KX26IfyX8Z.Ma6	kasir	t	nLl5ACnt5sCoqHOrm33KBT9BouQZv5PQL4ntAESUQB7JzQHfaIFi7z5gliOw	2026-09-09 05:59:00	2026-09-09 05:59:00
\.


--
-- TOC entry 5141 (class 0 OID 0)
-- Dependencies: 230
-- Name: activity_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.activity_logs_id_seq', 22, true);


--
-- TOC entry 5142 (class 0 OID 0)
-- Dependencies: 232
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.categories_id_seq', 3, true);


--
-- TOC entry 5143 (class 0 OID 0)
-- Dependencies: 228
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- TOC entry 5144 (class 0 OID 0)
-- Dependencies: 225
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- TOC entry 5145 (class 0 OID 0)
-- Dependencies: 248
-- Name: low_stock_notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.low_stock_notifications_id_seq', 1, true);


--
-- TOC entry 5146 (class 0 OID 0)
-- Dependencies: 217
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.migrations_id_seq', 13, true);


--
-- TOC entry 5147 (class 0 OID 0)
-- Dependencies: 250
-- Name: notification_reads_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.notification_reads_id_seq', 1, false);


--
-- TOC entry 5148 (class 0 OID 0)
-- Dependencies: 236
-- Name: product_change_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.product_change_logs_id_seq', 4, true);


--
-- TOC entry 5149 (class 0 OID 0)
-- Dependencies: 234
-- Name: products_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.products_id_seq', 4, true);


--
-- TOC entry 5150 (class 0 OID 0)
-- Dependencies: 244
-- Name: sale_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.sale_items_id_seq', 5, true);


--
-- TOC entry 5151 (class 0 OID 0)
-- Dependencies: 242
-- Name: sales_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.sales_id_seq', 4, true);


--
-- TOC entry 5152 (class 0 OID 0)
-- Dependencies: 246
-- Name: stock_adjustments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.stock_adjustments_id_seq', 2, true);


--
-- TOC entry 5153 (class 0 OID 0)
-- Dependencies: 240
-- Name: stock_ins_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.stock_ins_id_seq', 3, true);


--
-- TOC entry 5154 (class 0 OID 0)
-- Dependencies: 238
-- Name: suppliers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.suppliers_id_seq', 2, true);


--
-- TOC entry 5155 (class 0 OID 0)
-- Dependencies: 219
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: buana_store
--

SELECT pg_catalog.setval('public.users_id_seq', 3, true);


--
-- TOC entry 4886 (class 2606 OID 25809)
-- Name: activity_logs activity_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.activity_logs
    ADD CONSTRAINT activity_logs_pkey PRIMARY KEY (id);


--
-- TOC entry 4875 (class 2606 OID 25771)
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- TOC entry 4872 (class 2606 OID 25763)
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- TOC entry 4888 (class 2606 OID 25825)
-- Name: categories categories_name_unique; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_name_unique UNIQUE (name);


--
-- TOC entry 4890 (class 2606 OID 25823)
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- TOC entry 4882 (class 2606 OID 25799)
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 4884 (class 2606 OID 25801)
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- TOC entry 4880 (class 2606 OID 25789)
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- TOC entry 4877 (class 2606 OID 25781)
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 4919 (class 2606 OID 25962)
-- Name: low_stock_notifications low_stock_notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.low_stock_notifications
    ADD CONSTRAINT low_stock_notifications_pkey PRIMARY KEY (id);


--
-- TOC entry 4859 (class 2606 OID 25727)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 4923 (class 2606 OID 25988)
-- Name: notification_reads notification_reads_notification_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.notification_reads
    ADD CONSTRAINT notification_reads_notification_id_user_id_unique UNIQUE (notification_id, user_id);


--
-- TOC entry 4925 (class 2606 OID 25976)
-- Name: notification_reads notification_reads_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.notification_reads
    ADD CONSTRAINT notification_reads_pkey PRIMARY KEY (id);


--
-- TOC entry 4865 (class 2606 OID 25747)
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- TOC entry 4898 (class 2606 OID 25856)
-- Name: product_change_logs product_change_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.product_change_logs
    ADD CONSTRAINT product_change_logs_pkey PRIMARY KEY (id);


--
-- TOC entry 4893 (class 2606 OID 25837)
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- TOC entry 4896 (class 2606 OID 25846)
-- Name: products products_sku_unique; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_sku_unique UNIQUE (sku);


--
-- TOC entry 4914 (class 2606 OID 25924)
-- Name: sale_items sale_items_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.sale_items
    ADD CONSTRAINT sale_items_pkey PRIMARY KEY (id);


--
-- TOC entry 4909 (class 2606 OID 25917)
-- Name: sales sales_invoice_number_unique; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.sales
    ADD CONSTRAINT sales_invoice_number_unique UNIQUE (invoice_number);


--
-- TOC entry 4911 (class 2606 OID 25909)
-- Name: sales sales_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.sales
    ADD CONSTRAINT sales_pkey PRIMARY KEY (id);


--
-- TOC entry 4868 (class 2606 OID 25754)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 4917 (class 2606 OID 25941)
-- Name: stock_adjustments stock_adjustments_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.stock_adjustments
    ADD CONSTRAINT stock_adjustments_pkey PRIMARY KEY (id);


--
-- TOC entry 4904 (class 2606 OID 25884)
-- Name: stock_ins stock_ins_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.stock_ins
    ADD CONSTRAINT stock_ins_pkey PRIMARY KEY (id);


--
-- TOC entry 4907 (class 2606 OID 25902)
-- Name: stock_ins stock_ins_reference_number_unique; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.stock_ins
    ADD CONSTRAINT stock_ins_reference_number_unique UNIQUE (reference_number);


--
-- TOC entry 4900 (class 2606 OID 25877)
-- Name: suppliers suppliers_name_unique; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.suppliers
    ADD CONSTRAINT suppliers_name_unique UNIQUE (name);


--
-- TOC entry 4902 (class 2606 OID 25875)
-- Name: suppliers suppliers_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.suppliers
    ADD CONSTRAINT suppliers_pkey PRIMARY KEY (id);


--
-- TOC entry 4861 (class 2606 OID 25740)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 4863 (class 2606 OID 25738)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 4870 (class 1259 OID 25764)
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- TOC entry 4873 (class 1259 OID 25772)
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- TOC entry 4878 (class 1259 OID 25782)
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- TOC entry 4920 (class 1259 OID 25969)
-- Name: low_stock_notifications_product_id_status_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX low_stock_notifications_product_id_status_index ON public.low_stock_notifications USING btree (product_id, status);


--
-- TOC entry 4921 (class 1259 OID 25968)
-- Name: low_stock_notifications_status_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX low_stock_notifications_status_index ON public.low_stock_notifications USING btree (status);


--
-- TOC entry 4891 (class 1259 OID 25843)
-- Name: products_name_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX products_name_index ON public.products USING btree (name);


--
-- TOC entry 4894 (class 1259 OID 25844)
-- Name: products_sku_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX products_sku_index ON public.products USING btree (sku);


--
-- TOC entry 4912 (class 1259 OID 25915)
-- Name: sales_sold_at_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX sales_sold_at_index ON public.sales USING btree (sold_at);


--
-- TOC entry 4866 (class 1259 OID 25756)
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- TOC entry 4869 (class 1259 OID 25755)
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- TOC entry 4915 (class 1259 OID 25952)
-- Name: stock_adjustments_adjusted_at_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX stock_adjustments_adjusted_at_index ON public.stock_adjustments USING btree (adjusted_at);


--
-- TOC entry 4905 (class 1259 OID 25900)
-- Name: stock_ins_received_at_index; Type: INDEX; Schema: public; Owner: buana_store
--

CREATE INDEX stock_ins_received_at_index ON public.stock_ins USING btree (received_at);


--
-- TOC entry 4926 (class 2606 OID 25810)
-- Name: activity_logs 1; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.activity_logs
    ADD CONSTRAINT "1" FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4938 (class 2606 OID 25963)
-- Name: low_stock_notifications low_stock_notifications_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.low_stock_notifications
    ADD CONSTRAINT low_stock_notifications_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- TOC entry 4939 (class 2606 OID 25977)
-- Name: notification_reads notification_reads_notification_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.notification_reads
    ADD CONSTRAINT notification_reads_notification_id_foreign FOREIGN KEY (notification_id) REFERENCES public.low_stock_notifications(id) ON DELETE CASCADE;


--
-- TOC entry 4940 (class 2606 OID 25982)
-- Name: notification_reads notification_reads_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.notification_reads
    ADD CONSTRAINT notification_reads_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4928 (class 2606 OID 25857)
-- Name: product_change_logs product_change_logs_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.product_change_logs
    ADD CONSTRAINT product_change_logs_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- TOC entry 4929 (class 2606 OID 25862)
-- Name: product_change_logs product_change_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.product_change_logs
    ADD CONSTRAINT product_change_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- TOC entry 4927 (class 2606 OID 25838)
-- Name: products products_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.categories(id) ON DELETE RESTRICT;


--
-- TOC entry 4934 (class 2606 OID 25930)
-- Name: sale_items sale_items_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.sale_items
    ADD CONSTRAINT sale_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE RESTRICT;


--
-- TOC entry 4935 (class 2606 OID 25925)
-- Name: sale_items sale_items_sale_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.sale_items
    ADD CONSTRAINT sale_items_sale_id_foreign FOREIGN KEY (sale_id) REFERENCES public.sales(id) ON DELETE CASCADE;


--
-- TOC entry 4933 (class 2606 OID 25910)
-- Name: sales sales_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.sales
    ADD CONSTRAINT sales_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- TOC entry 4936 (class 2606 OID 25942)
-- Name: stock_adjustments stock_adjustments_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.stock_adjustments
    ADD CONSTRAINT stock_adjustments_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE RESTRICT;


--
-- TOC entry 4937 (class 2606 OID 25947)
-- Name: stock_adjustments stock_adjustments_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.stock_adjustments
    ADD CONSTRAINT stock_adjustments_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- TOC entry 4930 (class 2606 OID 25885)
-- Name: stock_ins stock_ins_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.stock_ins
    ADD CONSTRAINT stock_ins_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE RESTRICT;


--
-- TOC entry 4931 (class 2606 OID 25890)
-- Name: stock_ins stock_ins_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.stock_ins
    ADD CONSTRAINT stock_ins_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id) ON DELETE RESTRICT;


--
-- TOC entry 4932 (class 2606 OID 25895)
-- Name: stock_ins stock_ins_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: buana_store
--

ALTER TABLE ONLY public.stock_ins
    ADD CONSTRAINT stock_ins_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE RESTRICT;


-- Completed on 2026-10-02 13:04:12

--
-- PostgreSQL database dump complete
--

