import numpy as np
import math
import scipy.linalg as nla

def _VPAlgoQR_(A):
    # Algorithme QR Optimisé
    # A est une matrice de données carrée
    
    M = A.shape[0]
    lamb = list(0 for i in range(M))
    epsilon = 1e-5
    A = nla.hessenberg(A)
    k1 = M-1
    while(k1 >= 0):
        k0 = k1
        while (k0 >= 1 and abs(A[k0, k0-1]) > epsilon):
            k0 -= 1
        if (k0 == k1):
            lamb[k1] = A[k1, k1]
            k1 -= 1
        else:
            mu = A[k1, k1]
            for i in range(k0, k1+1):
                A[i, i] -= mu
            Q = nla.qr(A[k0:k1+1, k0:k1+1])[0]
            R = nla.qr(A[k0:k1+1, k0:k1+1])[1]
            A[k0:k1+1, k0:k1+1] = np.dot(R, Q)
            for i in range(k0, k1+1):
                A[i, i] += mu
    return lamb
                
# M = np.array([[2, -18, -38, -20], [-18, 2, -20, -38], [-38 , -20, 2, -18], [-20, -38, -18, 2]])
