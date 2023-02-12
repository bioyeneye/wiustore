import { Component } from '@angular/core';
import {Product} from "../../../models/product";
import {MatTableDataSource} from "@angular/material/table";
import {ProductService} from "../../../services/product.service";
import {error} from "@angular/compiler-cli/src/transformers/util";
import {NavigationExtras, Router} from "@angular/router";

@Component({
  selector: 'app-product-list',
  templateUrl: './product-list.component.html',
  styleUrls: ['./product-list.component.scss']
})
export class ProductListComponent {
  products: any[] = [];
  page:number = 1;
  hasMoreProduct = false;
  constructor(private productService: ProductService, private router: Router) {}
  ngOnInit(): void {
    this.loadMore();
  }

  loadMore() {
    this.productService.getProductsForMore(this.page)
      .subscribe((data)=>{
        this.products = this.products.concat(data.data?.items);
        if (++this.page == data.data?.pages) {
          this.hasMoreProduct = true;
        }else{
          this.hasMoreProduct = false;
        }
      });
  }

  goTo(product: Product){
    const navigationExtras: NavigationExtras = {state: product};
    this.router.navigate(['/products/' + product.id], navigationExtras);
  }
}
