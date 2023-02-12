import {Injectable} from '@angular/core';
import {HttpClient, HttpErrorResponse, HttpHeaders} from '@angular/common/http';
import {catchError, map} from 'rxjs/operators';
import {Observable, pipe, throwError} from 'rxjs';
import {Comment, Product} from "../models/product";

export interface ApiResponse<T> {
  hasResult: boolean;
  message?: string;
  data?: T;
}

export interface PaginationApiResponse<T> {
  hasResult: boolean;
  message?: string;
  data?: PaginationData<T>
}

export interface PaginationData<T> {
  total: number;
  pages: number,
  items: T[]
}

@Injectable({
  providedIn: 'root'
})
export class ProductService {
  private apiURL = 'http://wiu.edu/users/bio101/products.php';
  private apiCommentURL = 'http://wiu.edu/users/bio101/comments.php';
  private headers = new HttpHeaders().set('Content-Type', 'application/json');

  constructor(private http: HttpClient) {
  }

  getProductsForMore(page: number) : Observable<PaginationApiResponse<Product>> {
    return this.http.get<PaginationApiResponse<Product>>(`${this.apiURL}?page=${page}&size=5`)
      .pipe(
        map((res) => {
          return res;
        }),
        catchError(this.handleError)
      );
  }

  getProducts(page: number, size: number) : Observable<PaginationApiResponse<Product>> {
    return this.http.get<PaginationApiResponse<Product>>(`${this.apiURL}?page=${page}&size=${size}`)
      .pipe(
        map((res) => {
          return res;
        }),
        catchError(this.handleError)
      );
  }

  getProduct(id: string): Observable<ApiResponse<Product>> {
    return this.http.get<ApiResponse<Product>>(`${this.apiURL}?id=${id}`)
      .pipe(
      map((res) => {
        return res;
      }),
      catchError(this.handleError)
    );
  }

  getComments(id: string): Observable<ApiResponse<Comment[]>> {
    return this.http.get<ApiResponse<Comment[]>>(`${this.apiCommentURL}?product_id=${id}`)
      .pipe(
        map((res) => {
          return res;
        }),
        catchError(this.handleError)
      );
  }

  createProduct(product: Product) {
    return this.http.post(`${this.apiURL}`, product).pipe(
      catchError(this.handleError)
    );
  }

  createComment(comment: Comment): Observable<ApiResponse<Comment>> {
    const headers = new Headers({
      "Content-Type": "application/json",
      "Accept": "application/json"
    });
    return this.http.post<ApiResponse<Comment>>(`${this.apiCommentURL}`, JSON.stringify(comment))
      .pipe(
        map((res) => {
          return res;
        }),
        catchError(this.handleError)
      );
  }

  updateProduct(id: string, product: Product) {
    return this.http.put(`${this.apiURL}/${id}`, product).pipe(
      catchError(this.handleError)
    );
  }

  deleteProduct(id: string) {
    return this.http.delete(`${this.apiURL}/${id}`).pipe(
      catchError(this.handleError)
    );
  }

  private handleError(error: HttpErrorResponse) {
    if (error.error instanceof ErrorEvent) {
      console.error('An error occurred:', error.error.message);
    } else {
      console.error(
        `Backend returned code ${error.status}, ` +
        `body was: ${error.error}`);
    }
    return throwError({
      hasResult: false,
      message: 'Something bad happened; please try again later.'
    });
  }
}
