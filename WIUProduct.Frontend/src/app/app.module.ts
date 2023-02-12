import {NgModule} from '@angular/core';
import {BrowserModule} from '@angular/platform-browser';

import {AppRoutingModule} from './app-routing.module';
import {AppComponent} from './app.component';
import {ProductListComponent} from './components/products/product-list/product-list.component';
import {ProductDetailsComponent} from './components/products/product-details/product-details.component';
import {PostProductComponent} from './components/products/post-product/post-product.component';
import {AddCommentComponent} from './components/products/add-comment/add-comment.component';
import {HTTP_INTERCEPTORS, HttpClientModule} from "@angular/common/http";
import {ResponseInterceptor} from "./interceptors/ResponseInterceptor";
import {ProductService} from "./services/product.service";
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import {MatGridListModule} from "@angular/material/grid-list";
import {MatCardModule} from "@angular/material/card";
import {MatPaginatorModule} from "@angular/material/paginator";
import {ReactiveFormsModule} from "@angular/forms";

@NgModule({
  declarations: [
    AppComponent,
    ProductListComponent,
    ProductDetailsComponent,
    PostProductComponent,
    AddCommentComponent,
  ],
    imports: [
        BrowserModule,
        AppRoutingModule,
        BrowserAnimationsModule,
        MatGridListModule,
        MatCardModule,
        MatPaginatorModule,
        HttpClientModule,
        ReactiveFormsModule
    ],
  providers: [
    /*{
      provide: HTTP_INTERCEPTORS,
      useClass: ResponseInterceptor,
      multi: true
    },*/
    ProductService
  ],
  bootstrap: [AppComponent]
})
export class AppModule {
}
