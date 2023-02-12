export class Product {
  id: string | undefined;
  title: string | undefined;
  description: string | undefined;
  image: string | undefined;
  price: string | undefined;
  created_at: string | undefined;
  comments: string | undefined;
}

export interface Comment {
  id?: string;
  product_id: string;
  name: string;
  comment: string;
  created_at?: string;
  updated_at?: string;
}
